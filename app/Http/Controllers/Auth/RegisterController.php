<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Support\Facades\DB;
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
        $newUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $newUser->base_name = $this->createDataBase($newUser);
        $this->runMigrations($newUser->base_name);
        User::updated($newUser);
        return $newUser;
    }

    private function createDataBase($newUser)
    {
        $basename = $newUser->name . $newUser->id;
        $basename = strtoupper($basename);
        // $servername = config('envmysql.DB_HOST');
        // $username = config('envmysql.DB_USERNAME');
        // $password = config('envmysql.DB_PASSWORD');
        $return = true;



        try {
            // $conn = new PDO("mysql:host=$servername;dbname={$basename}", $username, $password);
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
        $mRunner = new MigrateRunner();
        $mRunner->createConfig($basename);
        $mRunner->run($basename);
    }
}
