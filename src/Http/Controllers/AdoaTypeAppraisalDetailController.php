<?php
namespace ProcessMaker\Package\PackageZjAdoa\Http\Controllers;

use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Http\Resources\ApiCollection;
use ProcessMaker\Package\PackageZjAdoa\Models\AdoaUsers;
use ProcessMaker\Package\PackageZjAdoa\Models\AdoaTypeAppraisalDetail;
use RBAC;
use Illuminate\Http\Request;
use URL;
use \DateTime;
use \DB;


class AdoaTypeAppraisalDetailController extends Controller
{
    public function index()
    {
        // return view('testpackage::index');
    }

    public function store(Request $request){
        $typeAppraisal = new AdoaTypeAppraisalDetail();
        $typeAppraisal->fill($request->json()->all());
        $typeAppraisal->saveOrFail();
        return $typeAppraisal;
    }
}
  
