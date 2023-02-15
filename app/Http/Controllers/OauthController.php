<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OauthController extends Controller
{
    public function redirect (Request $request) {
        $request->session()->put('state', $state = Str::random(40));
     
        $query = http_build_query([
            'client_id' => config('services.oauth.client_id'),
            'redirect_uri' => route('callback'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
            // 'prompt' => '', // "none", "consent", or "login"
        ]);
     
        return redirect('http://api.josue.test/oauth/authorize?'.$query);
    }

    public function callback(Request $request) {
        $state = $request->session()->pull('state');
        // return strlen($state); 
 
        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );
     
        $response = Http:: withHeaders([
            'Accept' => 'application/json'
        ]) -> asForm()->post('http://api.josue.test/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.oauth.client_id'),
            'client_secret' => config('services.oauth.client-secret'),
            'redirect_uri' => route('callback'),
            'code' => $request->code,
        ]);
     
        return $response->json();
    }
}
