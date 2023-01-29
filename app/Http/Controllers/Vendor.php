<?php

namespace App\Http\Controllers;

use App\Models\Vendor as ModelsVendor;
use EndyJasmi\Cuid;
use Illuminate\Http\Request;

class Vendor extends Controller
{
    //Create new vendor
    public function create(Request $request)
    {
        //Validate the incoming data
        [
            "vendor_name" => $vendor_name,
            "address" => $address
        ] = $request->validate([
            "vendor_name" => "required",
            "address" => "required"
        ]);

        //Create vendor model
        $vendor = new ModelsVendor();
        $vendor->vendor_name = $vendor_name;
        $vendor->address = $address;

        //Saving vendor to user
        $request->user()->vendors()->save($vendor);

        //Return vendor data as a response
        return $vendor->toArray();
    }

    //Create new invitation code
    public function createInvitationCode(Request $request, $id)
    {
        //Get the vendor
        $vendor = $request->user()->vendors()->findOrFail($id);

        //Create the cuid slug
        $cuid = strtoupper(Cuid::slug());

        //Save new cuid to vendor model
        $vendor->invitation_code = $cuid;
        $vendor->save();

        //Return vendor data with code as a response
        return $vendor->toArray() + ["invitation_code" => $cuid];
    }

    //Join to vendor
    public function join(Request $request, $code)
    {
        //Get the vendor first
        $vendor = ModelsVendor::where("invitation_code", $code)->firstOrFail();

        //Check if already joined
        if ($vendor->user()->where("user_id", $request->user()->id)->first()) {
            //Return error user already joined
            return response()->json([
                "error" => "User already joined"
            ], 403);
        }

        //Attach user to the vendor as a teacher
        $vendor->user()->attach($request->user(), ["role" => "teacher"]);

        //Return vendor data as a response
        return $vendor->toArray();
    }

    //Revoke invitation code
    public function revokeInvitationCode(Request $request, $id)
    {
        //Get the vendor
        $vendor = $request->user()->vendors()->findOrFail($id);

        //Remove invitation code from the vendor
        $vendor->invitation_code = null;
        $vendor->save();

        //Return vendor data with invitation code as a response
        return $vendor->toArray() + ["invitation_code" => null];
    }
}
