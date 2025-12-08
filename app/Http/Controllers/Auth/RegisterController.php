<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
    protected $redirectTo = RouteServiceProvider::HOME;

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
       $user = User::create([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'mobile_verified_at' => now(),
            'email' => $data['email'],
            'email_verified_at' => now(),
            'username' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->assignRole('user');

        if ($user->email) {
            $mailData['subject'] = 'Registration successfull!';
            $mailData['email'] = $user->email;
            $mailData['name'] = $user->name;
            $mailData['body'] = "Username: {$user->email}<br />Password: {$data['password']}";
            $mailData['url'] = url('login');
            $mailData['button'] = 'Login';
            $mailData['subbody'] = "You can also use your registered email & mobile as username.";
            $mailData['info'] = 'Note: don\'t share your login credentials & keep it confedential.';

            \Mail::send('emails.template', $mailData, function ($message) use ($mailData) {
                $message->subject($mailData['subject'])
                    ->to($mailData['email'], $mailData['name'])
                    ->cc(['amitmandal@andamanbliss.com', 'shivakumar@andamanbliss.com']);
            });
        }

        return $user;
    }


    public static function googleCallback($googleUser)
{
    // Example:
    // dd($googleUser->getId()); // check what values you get
    $user = User::firstOrCreate(
        ['email' => $googleUser->getEmail()],
        [
            'profile' => $googleUser->getAvatar(),
            'google_id' => $googleUser->getId(), 
            'name' => $googleUser->getName(),
            'mobile' => null,
            'mobile_verified_at' => now(),
            'email_verified_at' => now(),
            'username' => $googleUser->getEmail(),
            'password' => \Illuminate\Support\Str::password(12)
        ]
    );

    Auth::login($user);

    return redirect('/home');
}


    
}
