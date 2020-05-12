<?php

namespace App\Http\Controllers;

use App\Events\BlockEvent;
use App\Models\Session;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function block($session_id)
    {
        $session = Session::find($session_id);
        $session->update([
            'block' =>  1,
            'blocked_by' => auth()->id()
        ]);
        broadcast(new BlockEvent($session_id, true));
        return response(null, 201);
    }

    public function unblock($session_id)
    {
        $session = Session::find($session_id);
        $session->update([
            'block' =>  0,
            'blocked_by' => null
        ]);
        broadcast(new BlockEvent($session_id, false));
        return response(null, 201);
    }
}
