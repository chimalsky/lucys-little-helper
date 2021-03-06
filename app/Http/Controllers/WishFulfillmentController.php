<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Wish;
use App\Fulfillment;
use Illuminate\Http\Request;
use App\Mail\WishFulfillmentCreated;
use Illuminate\Support\Facades\Mail;    

class WishFulfillmentController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Fulfillment::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userQuery = $request->query('user');
        $user = $userQuery ? User::findOrFail($userQuery) : null;

        $fulfilledQuery = $request->query('fulfilled');

        $fulfillments = new Fulfillment;

        if ($user) {
            $fulfillments = $user->fulfillments();
        } 

        if ($fulfilledQuery) {
            $fulfillments = $fulfillments->fulfilled();
        } else {
            $fulfillments = $fulfillments->unfulfilled();
        }

        $fulfillments = $fulfillments->latest('created_at')->get();
        
        return view('wish.fulfillment.index', compact('user', 'fulfillments'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Wish $wish)
    {
        return view('wish.fulfillment.create', compact('wish'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Wish $wish)
    {
        $request->validate([
            'introduction' => 'required'
        ]);

        $fulfillment = Fulfillment::create([
            'wish' => $wish,
            'status' => 1,
            'giver' => Auth::user(),
        ]);

        $fulfillment->comment([
            'body' => $request->input('introduction')
        ], $fulfillment->giver);

        Mail::to($wish->user)->queue(new WishFulfillmentCreated($fulfillment));

        return redirect()->route('wish.fulfillment.show', compact('fulfillment'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Fulfillment  $fulfillment
     * @return \Illuminate\Http\Response
     */
    public function show(Fulfillment $fulfillment)
    {
        $wish = $fulfillment->wish;
        return view('wish.fulfillment.show', compact('wish', 'fulfillment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Fulfillment  $fulfillment
     * @return \Illuminate\Http\Response
     */
    public function edit(Fulfillment $fulfillment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Fulfillment  $fulfillment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fulfillment $fulfillment)
    {
        $fulfillment->status = 2;
        $fulfillment->save();

        //Mail::to($wish->user)->queue(new WishFulfillmentCreated($fulfillment));

        return redirect()->route('wish.fulfillment.show', compact('fulfillment'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Fulfillment  $fulfillment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fulfillment $fulfillment)
    {
        //
    }
}
