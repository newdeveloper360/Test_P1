<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Advice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
class AdviceController extends Controller {
    public function store(Request $request) {
        if (!$request->user()->tokenCan('profile:write')) return Response::failed('This session is read only.', null, 403);
        $data = $request->validate([
            'subject' => ['required', 'string', 'min:3', 'max:120'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);
        $advice = new Advice($data);
        $advice->user()->associate($request->user());
        $advice->save();
        return Response::success('Thank you. Your advice has been sent.', ['id' => $advice->id], 201);
    }
}
