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
}
