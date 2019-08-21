<?php

namespace App\Http\Controllers;

use App\Http\Controllers\MigrateRunner;
use DB;

class ProdutosController extends Controller
{
    public function index(){
        //Verifica se o Usuario esta Logado
        if(!(\Auth::check()))
        {
            echo "Necessário estar Logado para requisitar os Produtos";
            return;
        }
        //Recebe nome do banco do tenant
        $base_name = \Auth::user()->base_name;
        $mRunner = new MigrateRunner();
        //cria a configuração da conecção na memória
        $mRunner->createConfig($base_name);
        //recebe os produtos do tenant
        $produtos = DB::connection($base_name)->select('select * from products ');

        echo "<pre>";
        print_r($produtos);
        echo "</pre>";
    }
}
