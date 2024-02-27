@extends('adoa::layouts.layout')

@section('sidebar')
@include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_request')])
@endsection
@section('css')
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.css">


@endsection
@section('content')

<div class="col-sm-12">
    <h3>Requests from My Direct Reports</h3>
    <div class="card card-body table-card table-responsive" id="app-adoa">
        <div class="form-group">
            <div v-cloak>
                <div class="row text-center">
                    <div class="col-3 text-right">
                        <label>Please select the employee name:</label>
                    </div>
                    <div class="col-6 text-left">
                        <multiselect style="padding: 5px;" v-model="employeeSelectedParent" :options="employeesList" placeholder="Select one"
                            label="text" track-by="text" @select="updateCurrentEmployee(employeeSelectedParent, 1)"
                            @remove="unselected(1)">
                            <template slot="option" slot-scope="props">
                                <span class=""
                                    v-if="typeof props.option.meta.manager !== 'undefined' && props.option.meta.manager=='Y'"><i
                                        class="fas nav-icon fa-user "></i>
                                    @{{props.option.text}} (@{{props.option.meta.ein}})</span>
                                <span class="" v-else>@{{props.option.text}}
                                    (@{{props.option.meta.ein}})</span>
                            </template>
                        </multiselect>
                    </div>
                    <div class="col-3 text-left">
                        <label><b-spinner small variant="primary" v-show="spinners.select1"></b-spinner></label>
                    </div>
                    <div class="col-3 text-right">
                    </div>
                    <div class="col-6 text-left">
                        <div v-if="showLevels.select2">
                            <multiselect style="padding: 5px;" v-model="employeeSelectedParent2" :options="employeesList2"
                                placeholder="Select one" label="text" track-by="text"
                                @select="updateCurrentEmployee(employeeSelectedParent2, 2)" @remove="unselected(2)">
                                <template slot="option" slot-scope="props">
                                    <span class=""
                                        v-if="Object.keys(employeesList2).length > 0 && typeof props.option.meta.manager !== 'undefined' && props.option.meta.manager=='Y'"><i
                                            class="fas nav-icon fa-user "></i>
                                        @{{props.option.text}}</span>
                                </template>
                            </multiselect>
                        </div>
                    </div>
                    <div class="col-3 text-left">
                        <label v-show="spinners.select2"><b-spinner small variant="primary"></b-spinner></label>
                    </div>
                    <div class="col-3 text-right">
                    </div>
                    <div class="col-6">
                        <div v-if="showLevels.select3">
                            <multiselect style="padding: 5px;" v-model="employeeSelectedParent3" :options="employeesList3"
                                placeholder="Select one" label="text" track-by="text"
                                @select="updateCurrentEmployee(employeeSelectedParent3, 3)" @remove="unselected(3)">
                                <template slot="option" slot-scope="props">
                                    <span class=""
                                        v-if="typeof props.option.meta.manager !== 'undefined' && props.option.meta.manager=='Y'"><i
                                            class="fas nav-icon fa-user "></i>
                                        @{{props.option.text}}</span>
                                </template>
                            </multiselect>
                        </div>
                    </div>
                    <div class="col-3 text-right">
                        <label v-show="spinners.select3"><b-spinner small variant="primary"></b-spinner></label>
                    </div>
                    <div class="col-3 text-right">
                    </div>
                    <div class="col-6">
                        <div v-if="showLevels.select4">
                            <multiselect style="padding: 5px;" v-model="employeeSelectedParent4" :options="employeesList4"
                                placeholder="Select one" label="text" track-by="text"
                                @select="updateCurrentEmployee(employeeSelectedParent4, 4)" @remove="unselected(4)">
                                <template slot="option" slot-scope="props">
                                    <span class=""
                                        v-if="typeof props.option.meta.manager !== 'undefined' && props.option.meta.manager=='Y'"><i
                                            class="fas nav-icon fa-user "></i>
                                        @{{props.option.text}}</span>
                                </template>
                            </multiselect>
                        </div>
                    </div>
                    <div class="col-3 text-right">
                        <label v-show="spinners.select4"><b-spinner small variant="primary"></b-spinner></label>
                    </div>
                    <div class="col-3 text-right">
                    </div>
                    <div class="col-6">
                        <div v-if="showLevels.select5">
                            <multiselect style="padding: 5px;" v-model="employeeSelectedParent5" :options="employeesList5"
                                placeholder="Select one" label="text" track-by="text"
                                @select="updateCurrentEmployee(employeeSelectedParent5, 5)" @remove="unselected(5)">
                                <template slot="option" slot-scope="props">
                                    <span class=""
                                        v-if="typeof props.option.meta.manager !== 'undefined' && props.option.meta.manager=='Y'"><i
                                            class="fas nav-icon fa-user "></i>
                                        @{{props.option.text}}</span>
                                </template>
                            </multiselect>
                        </div>
                    </div>
                    <div class="col-3 text-right">
                        <label v-show="spinners.select5"><b-spinner small variant="primary"></b-spinner></label>
                    </div>
                    <input v-model="currentEmployee" class="invisible"></input>
                </div>
                <br />
                <div class="col-lg-12 col-md-12 col-sm-12" style="margin:10px;" v-if="currentEmployee > 0">
                    <div class="row">
                        <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                            <label for="currentEmployeeData.text" style="padding:5px;" class="label-color">
                                Employee Name
                            </label>
                            <input type="text" class="form-control" id="adoaEmployeeName" placeholder="Employee Name"
                                disabled v-model="currentEmployeeData.text">
                        </div>
                        <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                            <label for="currentEmployeeData.meta.ein" style="padding:5px;" class="label-color">
                                EIN
                            </label>
                            <input type="text" class="form-control" id="adoaEin" placeholder="EIN" disabled
                                v-model="currentEmployeeData.meta.ein">
                        </div>
                        <div class="form-group col col-lg-4 col-md-4 col-sm-12">
                            <label for="agencyName" style="padding:5px;" class="label-color">
                                Agency
                            </label>
                            <input type="text" class="form-control" id="agencyName" placeholder="Agency" disabled
                                v-model="currentEmployeeData.meta.agency_name">
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12" style="text-align: center">
                        <button id="btnGetList" class="btn btn-primary btn-sm" @click="getRequestsList">Get
                            List</button>
                    </div>
                </div>
            </div>
        </div>
        <table class="table table-striped table-hover" id="listRequests" width="100%" style="font-size: 13px">
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
            <tbody>
                @php
                 $count = 0;
                @endphp
            </tbody>
        </table>
    </div>
</div>


@section('js')
<script>
    window.temp_define = window['define'];
    window['define'] = undefined;
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script>
    window['define'] = window.temp_define;
</script>

<script type="text/javascript">
    $(".skip-navigation.alert.alert-info").hide();
    $(document).ready(function () {
        $('th').on("click", function (event) {
            if ($(event.target).is("input")) {
                event.stopImmediatePropagation();
            }
        });
        var table = $('#listRequests').DataTable({
            "initComplete": function () {
                count = 0;
                this.api().columns().every(function () {
                    if (this.index() != 0 && this.index() != 9) {
                        var title = this.header();
                        //replace spaces with dashes
                        title = $(title).html().replace(/[\W]/g, '-');
                        var column = this;
                        var select = $('<select id="' + title + '" class="select2"></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                //Get the "text" property from each selected data
                                //regex escape the value and store in array
                                var data = $.map($(this).select2('data'), function (value, key) {
                                    return value.text ? '^' + $.fn.dataTable.util.escapeRegex(value.text) + '$' : null;
                                });

                                //if no data selected use ""
                                if (data.length === 0) {
                                    data = [""];
                                }

                                //join array into string with regex or (|)
                                var val = data.join('|');

                                //search for the option(s) selected
                                column.search(val ? val : '', true, false).draw();
                            });

                        column.data().unique().sort().each(function (d, j) {
                            if (d != "") {
                                select.append('<option value="' + d + '">' + d + '</option>');
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
                        $('.select2').val(null).trigger('change');
                    }
                });
            },
            "order": [
                [0, "desc"]
            ],
            "pageLength": 25
        });

        function formatText(icon) {
            return $('<span><i class="fas ' + $(icon.element).data('icon') + '"></i> ' + icon.text + '</span>');
        };

        $('#filterEmployee').select2({
            width: "100%",
            templateSelection: formatText,
            templateResult: formatText
        });

        $('#filterEmployee').change(function () {
            alert($('#filterEmployee').val());
        });
    });
</script>
<script language="JavaScript">
    var USER_ID = '{{  $userId }}';
    var token = "{{ csrf_token() }}";
</script>
<script src="{{mix('/js/jsListManager.js', 'vendor/processmaker/packages/adoa')}}"></script>

@endsection
@endsection