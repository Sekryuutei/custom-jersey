<?php

namespace App\Http\View\Composers;

use App\Models\CartItem;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CartComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $count = CartItem::where('user_id', Auth::id())->count();
        } else {
            $count = CartItem::where('session_id', session()->getId())->count();
        }

        $view->with('cartItemCount', $count);
    }
}
