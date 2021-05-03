<?php

namespace App\Http\Controllers;

use App\Models\Pfsense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RulesController extends Controller
{
    public function index()
    {
       // sem gate

        if (!($user = \Auth()->user())) {
            return view('nologin');
        }
        $user->ip = $_SERVER['REMOTE_ADDR'];

        $rules = collect(Pfsense::listarNat($user->codpes));
        $rules = $rules->merge(Pfsense::listarFilter($user->codpes));
        //dd($rules);
        return view('index', compact('user', 'rules'));
    }

    public function allRules()
    {
        Gate::authorize('admin');

        $rules = Pfsense::listarRegras();
        return view('allRules', compact('rules'));
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
}
