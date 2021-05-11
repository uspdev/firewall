<?php

namespace App\Http\Controllers;

use App\Models\Pfsense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use \Spatie\Activitylog\Models\Activity;

class RulesController extends Controller
{
    public function index(Request $request)
    {
        // sem gate

        if (!($user = \Auth()->user())) {
            return view('nologin');
        }

        $user->ip = $_SERVER['REMOTE_ADDR'];

        $rules = Pfsense::listarRegras($user->codpes);
        $lastActivity = Activity::causedBy($user)->get()->last();

        # vamos gerar log na primeira atividade do dia
        if ($lastActivity && today()->diffInDays($lastActivity->created_at->startOfDay()) >= 1) {
            activity()->causedBy($user)->log('Primeira atividade do dia');
        }

        $activities = Activity::orderBy('created_at', 'DESC')->causedBy($user)->take(20)->get();

        return view('index', compact('user', 'rules', 'activities'));
    }

    public function allRules()
    {
        Gate::authorize('admin');

        return view('allRules', [
            'rules' => Pfsense::listarRegras(),
        ]);
    }

    public function updateRules(Request $request)
    {
        Gate::authorize('user');

        $user = \Auth()->user();
        $user->ip = $_SERVER['REMOTE_ADDR'];
        switch ($request->acao) {
            case 'atualizarNat':
                Pfsense::atualizarNat($user, $request['associated-rule-id']);
                break;
            case 'atualizarFilter':
                Pfsense::atualizarFilter($user, $request['descr']);
                break;
            case 'obterConfig':
                if ($user->level == 'admin') {
                    Pfsense::obterConfig(true);
                }
                break;
        }

        return redirect('');
    }

    public function activities()
    {
        Gate::authorize('admin');

        return view('atividades', [
            'activities' => Activity::orderBy('created_at', 'DESC')->get(),
        ]);
    }
}
