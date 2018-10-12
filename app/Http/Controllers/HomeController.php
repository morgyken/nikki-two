<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Posts;

use Mail;

use Stripe\Stripe;

class HomeController extends Controller
{

  private $email;

  private $subject;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Posts $email)
    {
      //  $this->middleware('auth');
      $this->email = $email;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $post = Posts::orderBy('created_at', 'desc')->paginate(3);

        return view('home', ['posts'=> $post]);
    }

    public function aboutus()
    {
        $post = Posts::orderBy('created_at', 'desc')->paginate(3);

        return view('aboutus');
    }

    public function support()
    {
        return view('support');
    }

    public function general()
    {
            return view('welcome');
    }


    public function posts(Request $request)
    {

        $post = new Posts;

        $post->urlpost = $request->urlpost;

        $post->email = $request->email;

        $this->email = $request->email;

        //send email here 
        $this->subject = 'Thank you for purchasing Unlocks';

        //$this->sendMail('emails.welcome');

        $post->save();

        session(['email' =>   $post->email ]);

        return redirect()->route('paypal');

    }

   public function paypal()
   {
       return view ('paypal-2', ['email'=> session('email')]);
   }

   public function sample()
   {
       return view ('index');
   }

   public function postPayment(Request $request)
   {
       $email = $this->email;
      // Set your secret key: remember to change this to your live secret key in production
      
      \Stripe\Stripe::setApiKey("sk_test_wR9YngHY8kyTzEcixpqikNT6");

      // Token is created using Checkout or Elements!
      // Get the payment token ID submitted by the form:
      $token = $_POST['stripeToken'];

          $charge = \Stripe\Charge::create([
          'amount' => 1*100,
          'currency' => 'usd',
          'description' => 'Hero Unlocks by'. session('email'),
          'source' => $token,
      ]);

          //upfdate the date 
          //update eloquent 
      $id = Posts::where('email', $email)->orderBy('created_at', 'desc')->firstOrFail();
      
    //update the current posts with token

      $posts = Posts::where('email', $email)
                        ->where('id',$id->id )
                        ->update(['token' => $token]);

      $this->subject = "Thank you for paying for unlocks";

      $this->sendMail('emails.payment');


      return redirect()->route('general');

   }

  
}
