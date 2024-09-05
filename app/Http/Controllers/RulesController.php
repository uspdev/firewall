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

        if (!Gate::allows('user')) {
            return view('nologin');
        }

        $user = \Auth()->user();
        $user->ip = $_SERVER['REMOTE_ADDR'];
        $connectionStatus = Pfsense::status();
        if($connectionStatus['status']){
            $rules = Pfsense::listarRegras($user->codpes);
        } else{
            return view('conectividade', [
                'msg' =>  "Impossível acessar o servidor SSH: " . $connectionStatus['msg'],
            ]);
        }
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
        $connectionStatus = Pfsense::status();
        if($connectionStatus['status']){
            return view('allRules', [
                'rules' => Pfsense::listarRegras(),
            ]);
        } else{
            return view('conectividade', [
                'msg' =>  "Impossível acessar o servidor SSH: " . $connectionStatus['msg'],
            ]);
        }
        
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
                Pfsense::atualizarFilter($user, $request['tracker']);
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
