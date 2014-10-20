<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	if (Auth::check() && Route::currentRouteName() == 'threadView') {
        $thread_id = Session::pull('thread_id');
        //register the thread as viewed at X time.

        //check if the thread has been viewed
        $view = ThreadView::where('user_id', Auth::user()->id)->where('thread_id', $thread_id)->first();

        if ($view)
        {
            $view->touch(); //update timestamp
        }
        else
        {
            //create new viewing entry. updated_at = last view, created_at = first view.
            $view = new ThreadView();
            $view->user_id = Auth::user()->id;
            $view->thread_id = $thread_id;
            $view->save();
        }
    }
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest())
	{
		if (Request::ajax())
		{
			return Response::make('Unauthorized', 401);
		}
		else
		{
			return Redirect::guest('login');
		}
	}
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});

/*
|
|	Roles and Permissions filters
|
*/
Entrust::routeNeedsPermission('admin*', 'admin_panel', View::make('errors.permissions'));

Route::filter('moderator', function()
{
    if (! Entrust::hasRole('Moderator') ) // Checks the current user
    {
        App::abort(404);
    }
});
