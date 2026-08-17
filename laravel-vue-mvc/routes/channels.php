<?php

use Illuminate\Support\Facades\Broadcast;

// Register the broadcast authentication endpoint (POST /broadcasting/auth).
Broadcast::routes();

// Private channel for the real-time pengingat notification bell.
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});