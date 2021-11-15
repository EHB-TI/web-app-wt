<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Attaches a role to a user by inserting record in the pivot table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Event $event, User $user)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'role' => ['required', Rule::in(['manager', 'seller'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validatedAttributes = $validator->validated();

        $user = User::firstWhere('email', $validatedAttributes['email']);
        $event = Event::findOrFail($event->id);

        $event->users()->attach($user->id, ['role' => $validatedAttributes['role']]);

        return response()->json(['data' => $event->members], Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => ['required', Rule::in(['manager', 'seller'])],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validatedAttributes = $validator->validated();

        $user = User::findOrFail($user->id);
        $event = Event::findOrFail($event->id);

        $event->users()->updateExistingPivot($user->id, ['role' => $validatedAttributes['role']]);

        return response()->json(['data' => $event->members], Response::HTTP_OK);
    }

    /**
     * Detaches the role from the user by deleting the record in the pivot table.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event, User $user)
    {
        $user = User::findOrFail($user->id);
        $event = Event::findOrFail($event->id);

        $event->users()->detach($user->id);

        return response()->noContent();
    }
}
