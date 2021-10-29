@extends('layouts.layout')

@section('sidebar')
    @include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_request')])
@endsection
@section('css')
    <style>
        [v-cloak] {
            display:none;
        }
        .label-color {
            color:#71A2D4;
        }
        .select2 > span, .select2-selection, .selection, .select2-selection__placeholder, .select2-selection__rendered {
            border-radius:0px !important;
        }
        #select2-adoaEmployee-results > .select2-results__option:hover {
            background-color: #71A2D4 !important;
            color:#fff;
        }
        .btn-primary {
            background-color: #71A2D4 !important;
            border:solid #71A2D4 !important;
        }

        #rwaList > tbody > tr > td > a > i {
            color: #71A2D4 !important;
        }
        #rwaList > tbody > tr > td > a > i:hover {
            color: #71A2D4 !important;
        }

        #rwaList > thead > tr {
            text-align: center;
            background-color: #71A2D4;
            color: #fff;

        }
        #rwaList > thead > tr > th {
            border-bottom: 3px solid  #505050 !important;
            padding-top:10px;
            padding-bottom:10px;
        }

        td > a :hover{
            color: #71A2D4 !important;
        }
        #rwaList_paginate > ul > li.paginate_button.page-item.active > a {
            background-color: #71A2D4 !important;
            color: #fff !important;
        }
        #rwaList_paginate > ul > li > a{
            color: #71A2D4 !important;
        }
        #rwaList_previous > a {
            color: #71A2D4 !important;
        }
        .page-item.active .page-link  {
            border: #71A2D4;
        }

        #rwaList_length > label,
        #rwaList_filter > label {
            color: #71A2D4 !important;
        }
        input[type=date]::-webkit-calendar-picker-indicator {
        }

        #select2-adoaEmployee-results > li:nth-child(1) {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
            border-top: 3px solid #71A2D4 !important;
            border-bottom: 3px solid #71A2D4 !important;
        }



    </style>
@endsection
@section('content')

<div class="container border" id="app" style="padding:20px;">
    <div class="row" v-cloak>
        <div style="text-align: center;width: 100%;">
            <h2> Print Remote Work Agreement </h2>
            <br>
            <br>
        </div>

        <div id="dropdown-container" class="col col-lg-8 col-md-8 col-sm-12 offset-lg-2 offset-md-2 " style="display: flex;overflow: visible;">
            <div class="col col-lg-5 col-md-5 col-sm-12">
                <label for="adoaEmployee" class="label-color">
                    Please select the employee name:
                </label>
            </div>
            <div class="col col-lg-6 col-md-6 col-sm-12">
                <select class="form-control" id="adoaEmployeeAdmin"  v-model="adoaEmployeeSelected" v-if="isSysAdmin"></select>
                <p class="col col-12" v-show="!isSysAdmin"><select class="form-control" id="adoaEmployeeLevel_1" level="1" style="width:100%;" v-model="adoaEmployeeSelected" v-if="!isSysAdmin"></select></p>
                <p class="col col-12" v-show="!isSysAdmin && employeeLevel.level_2.length>1"><select class="form-control" id="adoaEmployeeLevel_2" level="2" style="width:100%;" v-model="adoaEmployeeSelected"></select></p>
                <p class="col col-12" v-show="!isSysAdmin && employeeLevel.level_3.length>1"><select class="form-control" id="adoaEmployeeLevel_3" level="3" style="width:100%;" v-model="adoaEmployeeSelected"></select></p>
                <p class="col col-12" v-show="!isSysAdmin && employeeLevel.level_4.length>1"><select class="form-control" id="adoaEmployeeLevel_4" level="4" style="width:100%;" v-model="adoaEmployeeSelected"></select></p>
                <p class="col col-12" v-show="!isSysAdmin && employeeLevel.level_5.length>1"><select class="form-control" id="adoaEmployeeLevel_5" level="5" style="width:100%;" v-model="adoaEmployeeSelected"></select></p>
                <p class="col col-12" v-show="!isSysAdmin && employeeLevel.level_6.length>1"><select class="form-control" id="adoaEmployeeLevel_6" level="5" style="width:100%;" v-model="adoaEmployeeSelected"></select></p>
                <p class="col col-12" v-show="!isSysAdmin && employeeLevel.level_7.length>1"><select class="form-control" id="adoaEmployeeLevel_7" level="5" style="width:100%;" v-model="adoaEmployeeSelected"></select></p>
            </div>

            <div class="col col-1">
                <div v-if="loading" style="display:inherit;overflow: visible;">
                    <button class="btn btn-default">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                    </button>
                    <small class="text-secondary">Loading...</small>
                </div>
            </div>

        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" style="margin:10px;">
            <div class="row">
                <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                    <label for="adoaEmployeeName" style="padding:5px;" class="label-color">
                        Employee Name
                    </label>
                    <input type="text" class="form-control"
                    id="adoaEmployeeName"
                    placeholder="Employee Name" disabled
                    v-model="adoaEmployeeName">
                </div>
                <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                    <label for="adoaEin" style="padding:5px;" class="label-color">
                        EIN
                    </label>
                    <input type="text" class="form-control" id="adoaEin" placeholder="EIN" disabled v-model="adoaEin">
                </div>
                <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                    <label for="agencyName" style="padding:5px;" class="label-color">
                        Agency
                    </label>
                    <input type="text" class="form-control" id="agencyName" placeholder="Agency" disabled v-model="agencyName">
                </div>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12" style="text-align: center" v-if="adoaEmployeeSelected != ''">
                <button id="btnGetList" class="btn btn-primary btn-sm"  @click="getRwaList">Get List</button>
            </div>

        </div>

        <div id="listContainer" class="col-lg-12 col-md-12 col-sm-12" v-if="showList == true && rwaList.length > 0">
            <div class="table-responsive" style="padding:5px; border:1px solid #dfdfdf;boder-radius:5px;margin-top:auto;background-color:#fff;">
                <div style="text-align:center;"><h2>Remote Work Agreement</h2></div>

                <table id="rwaList" class="table table-striped table-hover table-sm" style="width: 100%;">
                </table>

            </div>
        </div>
        <div id="emptyRwaList" class="alert alert-info" role="alert" style="width:80%;margin:auto;" v-if="showList == true && rwaList.length <= 0">
            <span><i class="fas fa-info-circle"></i> There are no results to show.</span>
        </div>
    </div>
    {{-- Modal View PDF --}}
    <div class="modal fade" id="showPdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header text-right">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>

</div>


    @section('js')
        {{-- JqueryDataTable --}}

        <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
        <script>
            window.temp_define = window['define'];
            window['define']  = undefined;
        </script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
            <script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
        <script>
            window['define'] = window.temp_define;
        </script>

        <script>
            var app = new Vue({
                el: '#app',

                data() {
                    return {
                        adoaEmployee: {},
                        adoaEmployeeSelected:'',
                        adoaEmployeeName : '',
                        adoaUser : {!! json_encode($adoaUser, true) !!},
                        adoaEin : '',
                        agencyName : '',
                        dataTable: '',
                        rwaList : [],
                        showList : false,
                        currentUserId : {{ auth()->user()->id }},
                        currentUser : {!! auth()->user() !!},
                        isManager: {{ empty($isManager) ? 'false' : $isManager }},
                        isSysAdmin: {{ empty($isSysAdmin) ? 'false' : $isSysAdmin }},
                        agreementCollectionId: {{ $agreementCollectionId }},
                        loading:false,
                        loadingEmployeeList:false,
                        employeeLevel : {
                            "level_1":[],
                            "level_2":[],
                            "level_3":[],
                            "level_4":[],
                            "level_5":[],
                            "level_6":[],
                            "level_7":[]
                        }
                    }
                },
                methods : {
                    populateEmployeDropdown(level, employeeId){
                        this.loading = true;
                        ProcessMaker.apiClient
                        .get(
                            "/adoa/user/manager-employees/" + employeeId
                        )
                        .then(function(response) {
                            let newData= [{ 'id' : '', 'text' : '- Select -'}];
                            if(level == 1) {
                                let userData =[{
                                        'id' : app.currentUser.id,
                                        'text' : app.currentUser.firstname + ' ' + app.currentUser.lastname,
                                        'ein' : app.currentUser.meta.ein,
                                        'agency_name' : app.currentUser.meta.agency_name
                                    }];
                                    newData = newData.concat(userData);
                            }
                            let data = $.map(response.data, function (obj) {
                                obj.id = obj.id;
                                obj.text = obj.firstname + ' ' + obj.lastname;
                                return obj;
                            });

                            app.employeeLevel['level_' + level] = newData.concat(data);

                            $('#adoaEmployeeLevel_' + level)
                            .select2({
                                placeholder: 'Select an option',
                                data : app.employeeLevel['level_' + level]
                            })
                            .on('select2:select', function () {
                                app.rwaList = [];
                                app.showList = false;
                                let value = $("#adoaEmployeeLevel_" + level).select2('data');

                                app.adoaEmployeeSelected = value[0].id;
                                app.adoaEmployeeName = value[0].text;
                                app.adoaEin = value[0].ein;
                                app.agencyName = value[0].agency_name;

                                for (let index = level + 1 ; index < 8; index++) {
                                    app.employeeLevel['level_' + index] = [];
                                    $('#adoaEmployeeLevel_' + index)
                                    .select2({
                                        placeholder: 'Select an option',
                                        data : []
                                    });
                                    $('#adoaEmployeeLevel_' + index).empty();
                                }
                                app.populateEmployeDropdown(parseInt(level) + 1, parseInt(app.adoaEmployeeSelected));
                            });
                            app.loading = false;
                        })
                        .catch(function(response) {
                            app.loading = false;
                        });
                    },
                    populateEmployeeList(){
                        this.loading = true;
                        ProcessMaker.apiClient
                        .get(
                            "/adoa/employee-list/" + this.currentUserId
                        )
                        .then(function(response) {
                            $('#adoaEmployeeAdmin')
                            .select2({
                                placeholder: 'Select an option',
                                minimumInputLength: 2,
                                ajax: {
                                    url: "{{url('api/1.0/adoa/employee-list')}}/{{ auth()->user()->id }}",
                                    dataType: 'json',
                                    delay: 250,
                                    data: function (params) {
                                        return {
                                        searchTerm: params.term // search term
                                        };
                                    },
                                    headers: {
                                        "X-CSRF-TOKEN" : "{{ csrf_token() }}",
                                        "Content-Type" : "application/json",
                                    },
                                    processResults: function (response) {
                                        return {
                                            results: response
                                        };
                                    }
                                }
                            })
                            .on('select2:select', function () {
                                let currentLevel = 1;

                                app.rwaList = [];
                                app.showList = false;
                                var value = $("#adoaEmployeeAdmin").select2('data');
                                app.adoaEmployeeSelected = value[0].id;

                                if(self.adoaEmployeeSelected != '') {
                                    ProcessMaker.apiClient
                                    .get("/adoa/user/" + app.adoaEmployeeSelected)
                                    .then(response => {
                                        app.adoaEmployeeName = (response.data.firstname + ' ' + response.data.lastname).toUpperCase();
                                        app.adoaEin          = response.data.meta.ein;
                                        app.agencyName       = response.data.meta.agency_name;
                                    })
                                    .catch(response => {
                                        console.log(response);
                                    });
                                } else {
                                    app.adoaEmployeeName = '';
                                    app.adoaEin          = '';
                                    app.agencyName       = '';
                                }
                            });
                            app.loading = false;
                        })
                        .catch(function(response) {
                            app.loading = false;
                        });
                    },
                    getRwaList() {
                        ProcessMaker.apiClient
                        .post(
                            'adoa/rwa-collection/rwa-report',{'user_id': this.adoaEmployeeSelected}
                        )
                        .then(response => {
                            this.rwaList = response.data;
                            this.showList = true;
                            $('#rwaList').DataTable().destroy();
                        })
                        .catch(response => {
                            console.log(response);
                        });
                    }
                },
                created () {
                    if (this.isSysAdmin) {
                        this.populateEmployeeList();
                    } else {
                        this.populateEmployeDropdown(1, this.currentUserId);
                    }
                },
                mounted: function () {
                },
                watch: {
                    rwaList() {
                        this.$nextTick(function () {
                            this.dataTable = $('#rwaList').DataTable({
                                "responsive": true,
                                "processing": true,
                                "order": [[ 0, "desc" ]],
                                "data" : app.rwaList,
                                "columns": [
                                    { "title": "Request No.",  "data": "REQUEST_ID", "sortable": true, "defaultContent": "No Regitred", "class": "text-center" },
                                    { "title": "Full Name", "data": "ADOA_RWA_EMPLOYEE_NAME", "defaultContent": "", "class": "text-center",
                                        "render": function (data, type, row) {
                                            return row.ADOA_RWA_EMPLOYEE_NAME.toUpperCase();
                                        }
                                    },
                                    { "title": "EIN", "data": "ADOA_RWA_EIN", "defaultContent": "", "sortable": false, "class": "text-center"},
                                    { "title": "Type", "data": "ADOA_RWA_TYPE_REQUEST", "defaultContent": "", "sortable": false, "class": "text-center",
                                        "render":function (data, type, row) {
                                            switch (row.ADOA_RWA_TYPE_REQUEST) {
                                                case 'NEW':
                                                    return '<span class="badge badge-info" style="background-color: #71A2D4 !important;">NEW</span>';
                                                case 'RENEWAL':
                                                    return '<span class="badge badge-info" style="background-color: #6cc5b9 !important;">RENEWAL</span>';
                                                case 'TERMINATE':
                                                default:
                                                    return '<span class="badge badge-secondary">TERMINATE</span>';
                                            }
                                        }
                                    },
                                    { "title": "From", "data": "ADOA_RWA_REMOTE_AGREEMENT_START_DATE", "defaultContent": "", "class": "text-center"},
                                    { "title": "To", "data": "ADOA_RWA_REMOTE_AGREEMENT_END_DATE", "defaultContent": "", "class": "text-center"},
                                    {
                                        "title": "Actions", "data" : "", "sortable": false, "defaultContent": "", "class": "text-center",
                                        "render": function (data, type, row) {
                                            let html = '';
                                            html += '<a href="#"><i class="fas fa-eye" style="color: #71A2D4;" title="View PDF" onclick="viewPdf(' + row.REQUEST_ID + ', ' + row.FILE_ID + ');"></i></a>&nbsp;';
                                            html += '<a href="#"><i class="fas fa-print" style="color: #71A2D4;" title="Print PDF" onclick="printPdf(' + row.REQUEST_ID + ', ' + row.FILE_ID + ');"></i></a>&nbsp;';
                                            html += '<a href="/request/' + row.REQUEST_ID + '/files/' + row.FILE_ID + '"><i class="fas fa-download" style="color: #71A2D4;" title="Download PDF"></i></a>&nbsp;';
                                            return html;
                                        }
                                    }
                                ]
                            }).draw();
                        });

                    }
                }

            }).$mount('#app');

            function printPdf(request, file) {
                window.open('/adoa/view/' + request + '/' + file).print();
            }

            function viewPdf(request, file) {
                $('.modal-body').html('');
                $('.modal-body').html('<embed src="/adoa/view/' + request + '/' + file + '" frameborder="0" width="100%" height="800px">');
                $('#showPdf').modal('show');
            }

        </script>
    @endsection
@endsection
