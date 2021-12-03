@extends('layouts.layout')

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
</style>
@section('content')
<div class="col-sm-12">
    <h3>Agency {{ $agencyName }}</h3>
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
    <div class="modal fade" id="showPdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
    <div class="modal fade" id="showReassing" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                        obj.id   = obj.id;
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
        var table = $('#listRequestsAgency').DataTable({
            "initComplete": function () {
                count = 0;
                this.api().columns().every( function () {
                    if(this.index() != 0 && this.index() != 9) {
                        var title = this.header();
                        //replace spaces with dashes
                        title = $(title).html().replace(/[\W]/g, '-');
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

                        //initially clear select otherwise first option is selected
                        if (this.index() == 8) {
                            $('.select2').val(["ACTIVE"]).trigger('change');
                        } else {
                            $('.select2').val(null).trigger('change');
                        }
                    }
                });
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 25,
            "ajax": "{{ url('adoa/agency-dashboard') }}/{{ $groupId }}",
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
               "loadingRecords": "<div class='lds-ring'><div></div><div></div><div></div><div></div></div><br>Please wait, we are getting your information",
               "processing": "Loading...2"
            }
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
                    $('#showReassing').modal('show');
                    $('#showReassing .form-group').eq(1).show()
                    $('#buttonReassign').show();
                } else {
                    $('#reassignTitle').html('Reassign request # <strong id="strongRequestId">' + request + '</strong>');
                    $('#spanCurrentTask').html('');
                    $('#spanCurrentTask').html('The request does not have a valid task.');
                    $('#spanCurrentUser').html('');
                    $('#spanCurrentUser').html('The request does not have a valid user.');
                    $('#divTaskId').html('');
                    $('#showReassing').modal('show');
                    $('#showReassing .form-group').eq(1).hide()
                    $('#buttonReassign').hide();
                }
            });
        }

        $('#buttonReassign').click(function(event){
            if ($('#selectUserId').val() == null) {
                $('#divMessageError').css("display", "");
            } else {
                ProcessMaker.confirmModal('Confirm', '<div class="text-left">Are you sure that you want to reassign the request # ' + $('#strongRequestId').text() + ' from ' + $('#spanCurrentUser').text() + ' to ' + $('#selectUserId option:selected').text() + '?</div>', '', () => {
                    ProcessMaker.apiClient.put('adoa/update-task/' + $('#divTaskId').text(), {user_id: $('#selectUserId').val()});
                    ProcessMaker.alert('The request was reassigned successfully! Your browser will be reloaded!', 'success');
                    $('#showReassing').modal('hide');
                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                });
                $('#divMessageError').css("display", "none");
            }
        });

        $('#showReassing').on('hidden.bs.modal', function () {
            $('#selectUserId').val(null).trigger('change');
            $('#divMessageError').css("display", "none");
        })
    });
</script>
@endsection
@endsection
