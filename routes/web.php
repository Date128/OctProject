<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function () {
    return redirect('/users');
});

Route::get('/users', function () {
    $users = User::all();
    return view('users.index', compact('users'));
})->name('users.index');

Route::get('/users/create', function () {
    return view('users.create');
})->name('users.create');

Route::get('/users/{id}/edit', function ($id) {
    $user = User::findOrFail($id);
    return view('users.edit', compact('user'));
})->name('users.edit');

Route::post('/users', function (Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    return redirect('/users');
})->name('users.store');

Route::put('/users/{id}', function (Illuminate\Http\Request $request, $id) {
    $user = User::findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8',
    ]);

    $updateData = [
        'name' => $validated['name'],
        'email' => $validated['email'],
    ];

    if (!empty($validated['password'])) {
        $updateData['password'] = Hash::make($validated['password']);
    }

    $user->update($updateData);

    return redirect('/users');
})->name('users.update');

Route::delete('/users/{id}', function ($id) {
    User::destroy($id);
    return redirect('/users');
})->name('users.destroy');
