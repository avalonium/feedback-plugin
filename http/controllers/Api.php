<?php namespace Avalonium\Feedback\Http\Controllers;

use Request;
use Backend;
use Redirect;
use Illuminate\Routing\Controller;
use Avalonium\Feedback\Classes\AmoHelper;

/**
 * Api Controller
 */
class Api extends Controller
{
    public function updateAmoToken()
    {
        $data = Request::validate([
            'code' => 'required',
            'referer' => 'required'
        ]);

        if (AmoHelper::create()->updateAccessToken($data['code'], $data['referer'])) {
            return Redirect::to(Backend::url('system/settings/update/avalonium/feedback/settings'));
        }
    }
}
