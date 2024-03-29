<?php
namespace ProcessMaker\Package\PackageZjAdoa\Http\Controllers;

use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\PackageZjAdoa\Models\Sample;
use RBAC;
use Illuminate\Http\Request;
use URL;


class PackageZjAdoaController extends Controller
{
    public function index(){
        return view('package-zj-adoa::index');
    }

    public function fetch(Request $request){
        $query = Sample::query();

        $filter = $request->input('filter', '');
        if (!empty($filter)) {
            $filter = '%' . $filter . '%';
            $query->where(function ($query) use ($filter) {
                $query->Where('name', 'like', $filter);
            });
        }

        $order_by = $request->has('order_by') ? $order_by = $request->get('order_by') : 'name';
        $order_direction = $request->has('order_direction') ? $request->get('order_direction') : 'ASC';

        $response =
            $query->orderBy(
                $request->input('order_by', $order_by),
                $request->input('order_direction', $order_direction)
            )->paginate($request->input('per_page', 10));

        return new ApiCollection($response);
    }

    public function store(Request $request){
        $sample = new Sample();
        $sample->fill($request->json()->all());
        $sample->saveOrFail();
        return $sample;
    }

    public function update(Request $request, $license_generator){
        Sample::where('id', $license_generator)->update([
            'name' => $request->get("name"),
            'status' => $request->get("status")
            ]);
        return response([], 204);
    }

    public function destroy($license_generator){
        Sample::find($license_generator)->delete();
        return response([], 204);
    }

    public function generate($license_generator){

    }
}
