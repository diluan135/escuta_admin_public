<?php

namespace App\Http\Controllers;

use App\Events\MyEvent;
use App\Events\TestEvent;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function sendEvent(Request $request)
    {
        $message = $request->input('message', 'No message');
        event(new MyEvent('hello world'));
        return response()->json(['status' => 'Event sent']);
    }
}
