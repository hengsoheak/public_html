<?php
/**
 * Created by PhpStorm.
 * User: sopheak
 * Date: 2/17/2017 AD
 * Time: 12:16 AM
 */

namespace app\Http\Controllers\Front\Player;
use App\Models\SocialModels\SocialAccount;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\User;
use Auth;
use Intervention\Image\Facades\Image;

class PlayerController extends BaseController
{
    public function __construct()
    {

    }

    public function cards(Image $img) {

        $img = Image::make(public_path('image/card/test.jpg'));
        //$img->resize(320, 240);

        $social_user = SocialAccount::where(['user_id'=> Auth::user()->id, 'provider'=>Auth::user()->provider])->first();
        if(count($social_user) > 0 ){

            //public_path('image/card/watermark.png')
            //$img->fit(120, 90)->encode('png', 100);
            $base64Image =base64_encode(file_get_contents($social_user->avatar));//base64_encode($social_user->avatar);

            $image = Image::make($social_user->avatar)->save(public_path('/user_face/'.$social_user->name.'.jpg'));

            $img->insert($image, 'top-left', 20, 290);
            $img->save(public_path('image/card/new/bar3.jpg'));
            echo  '<html><img src="http://camvgo.com/image/card/new/bar3.jpg"></html>';
            return [true];
        }
        return [false];
    }
}
