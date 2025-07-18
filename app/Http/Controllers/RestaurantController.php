<?php

namespace App\Http\Controllers;


use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RestaurantController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $restaurants = Restaurant::all();
            return $this->successResponse($restaurants);
        } catch (\Exception $e) {
            return $this->errorResponse('Échec de la récupération des restaurants',500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|unique:restaurants',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $restaurant = Restaurant::create([
                'name' => $request->name,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'solde' => 0
            ]);

            return $this->sendSuccess($restaurant, 'Restaurant created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating restaurant', [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        if (!$restaurant) {
            return $this->sendNotFound();
        }

        return $this->sendSuccess($restaurant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'string|max:100',
            'address' => 'string|max:255',
            'phone_number' => 'string|max:20',
            'email' => 'email|unique:restaurants,email,' . $restaurant->id,
            'password' => 'string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator->errors());
        }

        try {
            $restaurant->update($request->all());
            return $this->sendSuccess($restaurant, 'Restaurant updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error updating restaurant', [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        try {
            $restaurant->delete();
            return $this->sendSuccess([], 'Restaurant deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting restaurant', [], 500);
        }
    }
}
