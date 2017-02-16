<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\SocialModels\SocialAccount;
use App\User;
use DB;
use Auth;
Use Redirect;
use Laracasts;


class SocialAuthController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('xss');
        $this->middleware('auth');
    }

    public function redirect($providerType)
    {
        switch ($providerType) {

            case 'facebook':
//                'facebook' => [
//                'client_id' => '102782740235682',
//                'client_secret' => '4ef4cebe7e4648aae71c624e7d45c481',
//                'redirect' => 'http://camvgo.com/callback/facebook',
//            ],
                $clientId = "102782740235682";
                $clientSecret = "4ef4cebe7e4648aae71c624e7d45c481";
                $redirectUrl = "http://camvgo.com/callback/facebook";

                $additionalProviderConfig = [
                    'site' => 'meta.stackoverflow.com'
                ];
                $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl,$additionalProviderConfig);//$additionalProviderConfig
                return Socialite::with('facebook')->setConfig($config)->redirect();

                //return Socialite::driver('facebook')->redirect();
                break;
            case 'google':
                return Socialite::driver('google')->redirect();
                break;
            case 'twitter':
                return Socialite::driver('twitter')->redirect();
                break;
            case 'linkedin':
                return Socialite::with('linkedin')->redirect();
                break;
        }
    }

    public function callback($providerType = [])
    {
        switch ($providerType) {
            case 'facebook':
                $providerUser = Socialite::driver('facebook')->stateless()->user();
                break;
            case 'google':
                $providerUser = Socialite::driver('google')->stateless()->user();
                break;
            case 'twitter':
                $providerUser = Socialite::driver('twitter')->stateless()->user();
                break;
            case 'linkedin':
                $providerUser = Socialite::driver('linkedin')->user();//Socialite::driver('linkedin')->user();

                break;
        }

        $authUser = $this->_findOrCreateUser($providerUser, $providerType);
        if (!$authUser) {
            flash()->overlay('An account for that email already exists!', 'Error');
            return redirect('/home');
        }
        Auth::login($authUser, true);
        return redirect('/home');
    }

    private function _findOrCreateUser($userProvider, $providerType)
    {
        DB::beginTransaction();
        $users = User::with(['SocialAccount' => function ($q) use ($providerType) {
            $q->where('provider', $providerType);
        }])->where('email', $userProvider->email)->first();

        if(!empty($users)) {

            if ((int)count($users->SocialAccount) == (int)0) {
                $this->createSocislAccount($users, $userProvider, $providerType);
            }
        }else {

            $users = new User();
        }

        $users->name = $userProvider->name;
        $users->email = $userProvider->email;
        if ($users->save()) {

            $this->createSocislAccount($users, $userProvider, $providerType);
            DB::commit();
            return $users;
        }
        return $users;
    }

    private function createSocislAccount($users , $userProvider, $providerType)
    {

        $findExistingAcc = SocialAccount::where('user_id', $users->id)->first();
        $socialAccount = new SocialAccount();
        if ((int)count($findExistingAcc) > (int)0) {
            $socialAccount = SocialAccount::where('id', $findExistingAcc->id)->first();
        }
        $socialAccount->provider = $providerType;
        $socialAccount->provider_user_id = $userProvider->id;
        $socialAccount->user_id = $users->id;
        $socialAccount->user_data = json_encode($userProvider->user);
        $socialAccount->avatar = strtolower($userProvider->avatar);
        return $socialAccount->save();
    }

}
