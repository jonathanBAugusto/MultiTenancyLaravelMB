<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MigrateRunner;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        //Cria novo usuário
        $newUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        //Aloca uma base de dados para ele e logo armazena seu nome na propriedade <base_name>
        $newUser->base_name = $this->createDataBase($newUser);
        //Salva o usuario com a propriedade
        $newUser->save();
        //roda os métodos necessários para o funcionamento e execução da migration
        $this->runMigrations($newUser->base_name);
        return $newUser;
    }

    private function createDataBase($newUser)
    {
        //Define o nome da Base a ser alocada ao Tenant
        $basename = $newUser->name . $newUser->id;
        //seta como lowercase como padrão
        $basename = strtolower($basename);
        $return = true;
        //Cria a base ou retorna uma mensagem
        try {
            $conn = DB::connection()->getPdo();
            $conn->setAttribute($conn::ATTR_ERRMODE, $conn::ERRMODE_EXCEPTION);
            $sql = "CREATE DATABASE IF NOT EXISTS {$basename}";
            $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . "" . $e->getMessage();
            $basename = "";
            $return = false;
        }
        $conn = null;

        return $return ? $basename : '';
    }

    private function runMigrations($basename){
        //Inicia a clase MigrateRunner,
        //que contera métodos para trabalhar com migrations na namespace de controllers
        $mRunner = new MigrateRunner();
        $mRunner->createConfig($basename);
        $mRunner->run($basename);
    }
}
