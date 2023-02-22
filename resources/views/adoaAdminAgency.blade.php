@extends('adoa::layouts.layout')

@section('sidebar')
    @include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_request')])
@endsection
@section('css')
    <link rel="stylesheet" href="{{mix('/css/package.css', 'vendor/processmaker/packages/adoa')}}">
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">
@endsection
<style media="screen">
    .lds-ring {
        display: inline-block;
        position: relative;
        width: 70px;
        height: 70px;
    }
    .lds-ring div {
        box-sizing: border-box;
        display: block;
        position: absolute;
        width: 50px;
        height: 50px;
        margin: 5px;
        border: 5px solid #fff;
        border-radius: 50%;
        animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        border-color: #A9A9A9 transparent transparent transparent;
    }
    .lds-ring div:nth-child(1) {
        animation-delay: -0.45s;
    }
    .lds-ring div:nth-child(2) {
        animation-delay: -0.3s;
    }
    .lds-ring div:nth-child(3) {
        animation-delay: -0.15s;
    }
    @keyframes lds-ring {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    .btn-primary {
        background-color: #71A2D4 !important;
        border:solid #71A2D4 !important;
    }
</style>
@section('content')
<div class="container"  style="margin:10px;">
    <div class="card col-lg-12 col-md-12 col-sm-12">
        <h4 class="">Search Criteria</h4>
        <form class="needs-validation" id="formSearchCriteria" novalidate>
            <div class="row">
                <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                    <label for="filterEmployeeName" style="padding:5px; font-size: 13px">Employee Name</label>
                    <select class="select2 form-control" id="filterEmployeeName"></select>
                </div>
                <div class="form-group col col-lg-2 col-md-2 col-sm-12">
                    <label for="filterEIN" style="padding:5px; font-size: 13px">EIN</label>
                    <input type="number" class="select2 form-control" id="filterEIN" placeholder="EIN" min="1">
                </div>
                <div class="form-group col col-lg-2 col-md-2 col-sm-12">
                    <label for="filterRequestId" style="padding:5px; font-size: 13px">Request ID</label>
                    <input type="number" class="select2 form-control" id="filterRequestId" placeholder="Request ID" min="1">
                </div>
            </div>
            <div class="row">
                <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                    <label for="filterDocument" style="padding:5px; font-size: 13px">Select Document(s)</label>
                    <select id="filterDocument" class="select2 form-control">
                        <option value="AZPerforms - My Coaching Notes">My Coaching Note</option>
                        <option value="AZPerforms - Coaching Notes for My Direct Reports">Coaching Note for My Direct Report</option>
                        <option value="AZPerforms - Self-Appraisal">Self-Appraisal</option>
                        <option value="AZPerforms - Informal Employee Appraisal">Informal Appraisal</option>
                        <option value="AZPerforms - Formal Employee Appraisal">Formal Employee Appraisal</option>
                        <option value="Remote Work - Initiate or Terminate Agreement">Initiate or Terminate Agreement</option>
                    </select>
                    <div class="invalid-feedback">
                        Please select at least one Agency.
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col col-lg-2 col-md-2 col-sm-12">
                    <label for="filterInitDate" style="padding:5px; font-size: 13px">From (Request Started)</label>
                    <input type="date" class="form-control" id="filterInitDate" onchange="rangeDates()" max="{!! date('Y-m-d') !!}">
                    <div class="invalid-feedback">
                        Please choose a date.
                    </div>
                </div>
                <div class="form-group col col-lg-2 col-md-2 col-sm-12">
                    <label for="filterEndDate" style="padding:5px; font-size: 13px">To (Request Started)</label>
                    <input type="date" class="form-control" id="filterEndDate" onchange="rangeDates()" max="{!! date('Y-m-d') !!}">
                    <div class="invalid-feedback">
                        Please choose a date.
                    </div>
                </div>
                <div class="form-group col col-lg-2 col-md-2 col-sm-12">
                    <label for="filterAgency" style="padding:5px; font-size: 13px">Agency</label>
                    <select id="filterAgency" class="select2 form-control">
                        @foreach ($agenciesArray as $agency)
                        <option value="{{ $agency }}">{{ $agency }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        Please select at least one Agency.
                    </div>
                </div>
                <div class="form-group col col-lg-2 col-md-2 col-sm-12">
                    <label for="filterLevel" style="padding:5px; font-size: 13px">Process Level</label>
                    <select id="filterLevel" class="select2 form-control">
                        @foreach ($levelsArray as $level)
                        <option value="{{ $level }}">{{ $level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col col-lg-2 col-md-2 col-sm-12">
                    <label for="filterStatus" style="padding:5px; font-size: 13px">Status</label>
                    <select id="filterStatus" class="select2 form-control">
                        <option value="ACTIVE">ACTIVE</option>
                        <option value="COMPLETED">COMPLETED</option>
                    </select>
                </div>
            </div>
            <div class="form-group col col-lg-12 col-md-12 col-sm-12" style="text-align:left;padding-top:5px;">
                <div style="color: red; font-size: 13px">
                    <i>
                        <strong>NOTE:</strong>  The maximum search range is 90 days. Select the “From Date” first, and adjust the “To Date” as needed.
                    </i>
                </div>
            </div>
            <div class="row">
                <div class="form-group col col-lg-3 col-md-3 col-sm-12">
                    <button id="btnGetList" class="btn btn-primary btn-block btn-sm" type="submit">Get List</button>
                </div>
                <div class="form-group col col-lg-3 col-md-3 col-sm-12">
                    <button id="btnClear" class="btn btn-info btn-block btn-sm" type="button">Clear</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="col-sm-12">
    <h3 id="titleAgency">No agency selected</h3>
    <div class="card card-body table-card table-responsive" id="app-adoa">
        <table class="table table-striped table-hover" id="listRequestsAgency" width="100%" style="font-size: 13px">
            <thead class="table-primary">
                <tr>
                    <th scope="col" class="apply-filter" width="5%">#</th>
                    <th scope="col" class="apply-filter" width="10%">Process</th>
                    <th scope="col" class="apply-filter" width="10%">Employee Name</th>
                    <th scope="col" class="apply-filter" width="10%">EIN</th>
                    <th scope="col" class="apply-filter" width="10%">Started</th>
                    <th scope="col" class="apply-filter" width="10%">Completed</th>
                    <th scope="col" class="apply-filter" width="15%">Current Task</th>
                    <th scope="col" class="apply-filter" width="13%">Current User</th>
                    <th scope="col" class="apply-filter" width="7%">Status</th>
                    <th scope="col" class="text-center" width="10%"><strong>Options</strong></th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="modal fade" id="showPdf" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="showReassing" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reassignTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger alert-dismissible fade show" style="display: none" role="alert" id="divMessageError">
                        The <strong>Reassign to:</strong> field is required.
                    </div>
                    <div class="form-group">
                        <strong>Current Task: </strong><br><span id="spanCurrentTask"></span>
                    </div>
                    <div class="form-group">
                        <strong>Current User: </strong><br><span id="spanCurrentUser"></span>
                    </div>
                    <div class="form-group">
                        <label for="selectUserId"><strong>Reassign to:</strong></label>
                        <select class="select2 form-control" id="selectUserId" required>
                        </select>
                    </div>
                    <div style="display: none;" id="divTaskId">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="buttonReassign">Reassign</button>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js')
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
<script type="text/javascript">
    $("#btnGetList").hide();
    $("#btnClear").hide();
    $('#filterDocument').prop('disabled', true);
    $('#filterAgency').prop('disabled', true);
    $('#filterStatus').prop('disabled', true);
    $(".skip-navigation.alert.alert-info").hide();
    var titleTable = [];
    $(document).ready( function () {
        $('th').on("click", function (event) {
            if($(event.target).is("input")){
                event.stopImmediatePropagation();
            }
        });

        let currentUser = {!! Auth::user() !!};
        $("#selectUserId").select2({
            ajax: {
                url: '/api/1.0/adoa/get-users-agency',
                dataType: 'json',
                data: function (data) {
                    return {
                        searchTerm: data.term,
                        agency: currentUser.meta.agency,
                        employee_process_level: currentUser.meta.employee_process_level
                    };
                },
                processResults: function (response) {
                    let list = $.map(response, function (obj) {
                        obj.id = obj.id;
                        if (obj.agency == 'ALL') {
                            obj.text = obj.firstname + ' ' + obj.lastname  + ' - '  + obj.ein + ' - ' + obj.username;
                        } else {
                            obj.text = obj.firstname + ' ' + obj.lastname  + ' - '  + obj.agency + ' - ' + obj.username;
                        }
                        return obj;
                       });
                    return {
                        results: list
                    };
                },
                headers: {
                    "X-CSRF-TOKEN" : "{{ csrf_token() }}",
                    "Content-Type" : "application/json",
                },
                cache: true
            },
            placeholder: 'Select user...',
            width: '100%',
            minimumInputLength: 2
        });

        $("#filterEmployeeName").select2({
            ajax: {
                url: '/api/1.0/adoa/get-users-agency',
                dataType: 'json',
                data: function (data) {
                    return {
                        searchTerm: data.term,
                        agency: currentUser.meta.agency,
                        employee_process_level: currentUser.meta.employee_process_level
                    };
                },
                processResults: function (response) {
                    let list = $.map(response, function (obj) {
                        obj.id = obj.ein;
                        if (obj.agency == 'ALL') {
                            obj.text = obj.firstname + ' ' + obj.lastname  + ' - '  + obj.ein + ' - ' + obj.username;
                        } else {
                            obj.text = obj.firstname + ' ' + obj.lastname  + ' - '  + obj.agency + ' - ' + obj.username;
                        }
                        return obj;
                       });
                    return {
                        results: list
                    };
                },
                headers: {
                    "X-CSRF-TOKEN" : "{{ csrf_token() }}",
                    "Content-Type" : "application/json",
                },
                cache: true
            },
            placeholder: 'Employee Name',
            width: '100%',
            minimumInputLength: 2
        });
        
        var table = $('#listRequestsAgency').DataTable({
            "initComplete": function () {
                count = 0;
                this.api().columns().every( function () {
                    if(this.index() != 0 && this.index() != 9) {
                        var title = this.header();
                        //replace spaces with dashes
                        title = $(title).html().replace(/[\W]/g, '-');
                        titleTable[this.index()] = title;
                        var column = this;
                        var select = $('<select id="' + title + '" class="select2"></select>')
                        .appendTo( $(column.header()).empty() )
                        .on( 'change', function () {
                            //Get the "text" property from each selected data
                            //regex escape the value and store in array
                            var data = $.map( $(this).select2('data'), function( value, key ) {
                                return value.text ? '^' + $.fn.dataTable.util.escapeRegex(value.text) + '$' : null;
                            });

                            //if no data selected use ""
                            if (data.length === 0) {
                                data = [""];
                            }

                            //join array into string with regex or (|)
                            var val = data.join('|');

                            //search for the option(s) selected
                            column.search( val ? val : '', true, false ).draw();
                        });

                        column.data().unique().sort().each(function (d, j) {
                            if (d != "") {
                                select.append( '<option value="' + d + '">' + d + '</option>' );
                            }
                        });

                        //use column title as selector and placeholder
                        $('#' + title).select2({
                            multiple: true,
                            closeOnSelect: true,
                            placeholder: title,
                            width: '100%'
                        });

                        $('.select2').val(null).trigger('change');
                    }
                });
            },
            "destroy": true,
            "columns": [
                {data: 'request_id', className: 'text-left'},
                {data: 'process_name', className: 'text-left'},
                {data: 'employee_name', className: 'text-left'},
                {data: 'employee_ein', className: 'text-left'},
                {data: 'started', className: 'text-left'},
                {data: 'completed', className: 'text-left'},
                {data: 'current_task', className: 'text-left'},
                {data: 'current_user', className: 'text-left'},
                {data: 'status', className: 'text-left'},
                {data: 'options', className: 'text-right'}
            ],
            'language':{
               "loadingRecords": "<div class='lds-ring'><div></div><div></div><div></div><div></div></div><br>Please wait, we are getting your information"
            }
        });

        $('#filterAgency').select2({
            multiple: true,
            closeOnSelect: true,
            placeholder: 'Agency',
            width: '100%',
            maximumSelectionLength: 3
        });

        $('#filterLevel').select2({
            multiple: true,
            closeOnSelect: true,
            placeholder: 'Process Level',
            width: '100%'
        });

        $('#filterStatus').select2({
            multiple: true,
            closeOnSelect: true,
            placeholder: 'Status',
            width: '100%'
        });

        $('#filterDocument').select2({
            multiple: true,
            closeOnSelect: true,
            placeholder: 'Document(s)',
            width: '100%'
        });

        window.reassign = function(request, task) {
            ProcessMaker.apiClient.get('adoa/get-task-agency/' + task).then(responseTask => {
                if (responseTask.data.length > 0) {
                    $('#reassignTitle').html('Reassign request # <strong id="strongRequestId">' + request + '</strong>');
                    $('#spanCurrentTask').html('');
                    $('#spanCurrentTask').html(responseTask.data[0].element_name);
                    $('#spanCurrentUser').html('');
                    $('#spanCurrentUser').html(responseTask.data[0].firstname + ' ' + responseTask.data[0].lastname);
                    $('#divTaskId').html('');
                    $('#divTaskId').html(responseTask.data[0].id);
                    $('#selectUserId').val(null).trigger('change');
                    $('#showReassing').modal('show');
                    $('#showReassing .form-group').eq(1).show();
                    $('#buttonReassign').show();
                } else {
                    $('#reassignTitle').html('Reassign request # <strong id="strongRequestId">' + request + '</strong>');
                    $('#spanCurrentTask').html('');
                    $('#spanCurrentTask').html('The request does not have a valid task.');
                    $('#spanCurrentUser').html('');
                    $('#spanCurrentUser').html('The request does not have a valid user.');
                    $('#divTaskId').html('');
                    $('#selectUserId').val(null).trigger('change');
                    $('#showReassing').modal('show');
                    $('#showReassing .form-group').eq(1).hide();
                    $('#buttonReassign').hide();
                }
            });
        }

        $('#buttonReassign').click(function(event){
            if ($('#selectUserId').val() == null) {
                $('#divMessageError').css("display", "");
            } else {
                $('#showReassing').modal('hide');
                ProcessMaker.confirmModal('Confirm', '<div class="text-left">Are you sure that you want to reassign the request # ' + $('#strongRequestId').text() + ' from ' + $('#spanCurrentUser').text() + ' to ' + $('#selectUserId option:selected').text() + '?</div>', '', () => {
                    ProcessMaker.apiClient.put('adoa/update-task/' + $('#divTaskId').text(), {user_id: $('#selectUserId').val()});
                    ProcessMaker.alert('The request was reassigned successfully! Your browser will be reloaded!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                });
                $('#divMessageError').css("display", "none");
            }
        });

        $('#showReassing').on('hidden.bs.modal', function () {
            $('#divMessageError').css("display", "none");
        });

        $('#filterLevel').prop('disabled', true);

        $('#filterAgency').change(function() {
            if ($('#filterAgency').val() != '') {
                $('#filterLevel').prop('disabled', false);
                $('#filterLevel').select2({
                    multiple: true,
                    closeOnSelect: true,
                    placeholder: 'Process Level',
                    width: '100%',
                    matcher: function(term, text, option) {
                        let value = '';
                        $('#filterAgency').val().forEach(function(item, index, array) {
                            if (text.id.substr(0, 2) == item) {
                                value = text;
                            }
                        });
                        return value;
                    }
                });
            } else {
                $('#filterLevel').val('').trigger('change');
                $('#filterLevel').prop('disabled', true);
                $('#filterLevel').select2({
                    multiple: true,
                    closeOnSelect: true,
                    placeholder: 'Process Level',
                    width: '100%'
                });
            }
        });

        window.rangeDates = function() {
            var init = new Date(filterInitDate.value);
            var end = new Date(filterEndDate.value);
            var initAux = new Date(filterInitDate.value);
            var today = new Date();

            init.setDate(init.getDate() + 91);

            if (init > today) {
                var nd = ("0" + (today.getDate())).slice(-2);
                var m = ("0" + (today.getMonth() + 1)).slice(-2);
                var y = today.getFullYear();
                if (m == 13) {
                    m = 1;
                }
                filterEndDate.max = y + '-' + m + '-' + nd;
                filterEndDate.min = filterInitDate.value;
            } else {
                var nd = ("0" + (init.getDate())).slice(-2);
                var m = ("0" + (init.getMonth() + 1)).slice(-2);
                var y = init.getFullYear();
                if (m == 13) {
                    m = 1;
                }
                filterEndDate.max = y + '-' + m + '-' + nd;
                filterEndDate.min = filterInitDate.value;
            }

            if (filterEndDate.value == '') {
                filterEndDate.value = y + '-' + m + '-' + nd;
            } else if (end < initAux || init < end) {
                filterEndDate.value = y + '-' + m + '-' + nd;
            }
        }

        window.validateButton = function() {
            if ($("#filterEmployeeName").val() != null || $("#filterEIN").val().length > 0 || $("#filterRequestId").val().length > 0 || $("#filterDocument").val().length > 0 || $("#filterInitDate").val().length > 0 || $("#filterEndDate").val().length > 0 || $("#filterAgency").val().length > 0 || $("#filterLevel").val().length > 0 || $("#filterStatus").val().length > 0) {
                $("#btnGetList").show();
                $("#btnClear").show();
            } else {
                $("#btnGetList").hide();
                $("#btnClear").hide();
            }

            if ($("#filterEmployeeName").val() != null || $("#filterEIN").val().length > 0) {
                $('#filterDocument').prop('disabled', false);
            } else {
                $('#filterDocument').prop('disabled', true);
            }

            if ($("#filterInitDate").val() != '' && $("#filterInitDate").val() != '') {
                $('#filterAgency').prop('disabled', false);
                $('#filterAgency').prop('required',true);
                $('#filterStatus').prop('disabled', false);
            } else {
                $('#filterAgency').prop('disabled', true);
                $('#filterAgency').prop('required',false);
                $('#filterStatus').prop('disabled', true);
            }
        }

        $("#formSearchCriteria").change(function() {
            validateButton();
        });

        $("#filterEIN").keyup(function() {
            validateButton();
        });

        $("#filterRequestId").keyup(function() {
            validateButton();
        });

        $("#btnClear").click(function() {
            $("#filterEmployeeName").val("").trigger('change');
            $("#filterEIN").val("");
            $("#filterRequestId").val("");
            $("#filterDocument").val("").trigger('change');
            $("#filterInitDate").val("").trigger('change');
            $("#filterEndDate").val("").trigger('change');
            $("#filterAgency").val("").trigger('change');
            $("#filterLevel").val("").trigger('change');
            $("#filterStatus").val("").trigger('change');
            validateButton();
        });
    });

    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        event.preventDefault();
                        event.stopPropagation();
                        if ($('#filterAgency').val() != '') {
                            $('#titleAgency').html('');
                            $('#titleAgency').html('Agency ' + $('#filterAgency').val());
                        } else {
                            $('#titleAgency').html('No agency selected');
                        }
                        var table = $('#listRequestsAgency').DataTable({
                            "initComplete": function () {
                                count = 0;
                                this.api().columns().every( function () {
                                    if(this.index() != 0 && this.index() != 9) {
                                        var title = titleTable[this.index()];
                                        //replace spaces with dashes
                                        var column = this;
                                        var select = $('<select id="' + title + '" class="select2"></select>')
                                        .appendTo( $(column.header()).empty() )
                                        .on( 'change', function () {
                                            //Get the "text" property from each selected data
                                            //regex escape the value and store in array
                                            var data = $.map( $(this).select2('data'), function( value, key ) {
                                                return value.text ? '^' + $.fn.dataTable.util.escapeRegex(value.text) + '$' : null;
                                            });

                                            //if no data selected use ""
                                            if (data.length === 0) {
                                                data = [""];
                                            }

                                            //join array into string with regex or (|)
                                            var val = data.join('|');

                                            //search for the option(s) selected
                                            column.search( val ? val : '', true, false ).draw();
                                        });

                                        column.data().unique().sort().each(function (d, j) {
                                            if (d != "") {
                                                select.append( '<option value="' + d + '">' + d + '</option>' );
                                            }
                                        });

                                        //use column title as selector and placeholder
                                        $('#' + title).select2({
                                            multiple: true,
                                            closeOnSelect: true,
                                            placeholder: title,
                                            width: '100%'
                                        });

                                        $('#listRequestsAgency .select2').val(null).trigger('change');
                                    }
                                });
                            },
                            "order": [[ 0, "desc" ]],
                            "pageLength": 25,
                            "destroy": true,
                            "ajax": {
                                "url": "{{ url('adoa/agency-dashboard') }}/{{ $groupId }}",
                                "type": "GET",
                                "data": {
                                    "filterEmployeeName": $("#filterEmployeeName").val(),
                                    "filterEIN": $("#filterEIN").val(),
                                    "filterRequestId": $("#filterRequestId").val(),
                                    "filterDocument": $("#filterDocument").val(),
                                    "filterInitDate": $("#filterInitDate").val(),
                                    "filterEndDate": $("#filterEndDate").val(),
                                    "filterAgency": $("#filterAgency").val(),
                                    "filterStatus": $("#filterStatus").val(),
                                    "filterLevel": $("#filterLevel").val()
                                }
                            },
                            "columns": [
                                {data: 'request_id', className: 'text-left'},
                                {data: 'process_name', className: 'text-left'},
                                {data: 'employee_name', className: 'text-left'},
                                {data: 'employee_ein', className: 'text-left'},
                                {data: 'started', className: 'text-left'},
                                {data: 'completed', className: 'text-left'},
                                {data: 'current_task', className: 'text-left'},
                                {data: 'current_user', className: 'text-left'},
                                {data: 'status', className: 'text-left'},
                                {data: 'options', className: 'text-right'}
                            ],
                            'language':{
                            "loadingRecords": "<div class='lds-ring'><div></div><div></div><div></div><div></div></div><br>Please wait, we are getting your information"
                            }
                        });
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
@endsection
@endsection
