<?php

namespace App\Http\Controllers;

use App\Models\Course as ModelsCourse;
use EndyJasmi\Cuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Course extends Controller
{
    //Create new course
    public function create(Request $request)
    {
        //Validate the incoming data
        [
            "vendor_id" => $vendor_id,
            "pic" => $pic,
            "title" => $title,
            "description" => $description,
            "price" => $price,
            "start_at" => $start_at,
            "teachers" => $teachers
        ] = $request->validate([
            "vendor_id" => "required|numeric",
            "pic" => "file|image|size:2048",
            "title" => "required",
            "description" => "required",
            "price" => "required|numeric",
            "start_at" => "required|date_format:d-m-Y",
            "teachers" => "required|array|min:1",
            "teachers.*" => "numeric"
        ]) + ["pic" => null];

        //Get the vendor
        $vendor = $request->user()->vendors()->where("vendors.id", $vendor_id)->wherePivot("role", "admin")->firstOrFail();

        //Get teachers
        $teachers = $vendor->users()->whereIn("users.id", $teachers)->get();

        $results = [];

        //Save course and attach each teachers using transaction
        DB::transaction(function () use (&$results, $title, $description, $price, $start_at, $vendor, $teachers) {
            //Create course model
            $course = new ModelsCourse();
            $course->title = $title;
            $course->description = $description;
            $course->price = $price;
            $course->start_at = $start_at;

            $vendor->courses()->save($course);

            //Attach teachers to course
            $attached_teacher = [];
            foreach ($teachers->toArray() as $teacher) {
                $attached_teacher[$teacher["id"]] = [
                    "role" => "teacher",
                    "member_id" => strtoupper(Cuid::slug())
                ];
            }

            $course->members()->attach($attached_teacher);

            $results = $course->load("members")->load("vendor")->toArray();
        });

        return $results;
    }
}
