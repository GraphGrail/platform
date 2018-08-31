<?php
/**
 * @author Juriy Panasevich <u.panasevich@graphgrail.com>
 */

namespace App\Http\Middleware;


use App\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SetLocale
{

    public function handle($request, \Closure $next)
    {
        /** @var User $user */
        if (!$user = Auth::user()) {
            return $next($request);
        }
        App::setLocale($user->locale);

        return $next($request);
    }
}
