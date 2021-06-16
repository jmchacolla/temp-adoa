@extends('layouts.layout')

@section('sidebar')
    @include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_request')])
@endsection
@section('css')
    <link rel="stylesheet" href="{{mix('/css/package.css', 'vendor/processmaker/packages/adoa')}}">
    <link href="https://nightly.datatables.net/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css">
@endsection
@section('content')
<div class="col-sm-12">
    <h3>Agency {{ $agencyName }}</h3>
    <div class="card card-body table-card table-responsive" id="app-adoa">
        <table class="table table-striped table-hover" id="listRequestsAgency" width="100%" style="font-size: 13px">
            <thead class="table-primary">
                <tr>
                    <th scope="col" class="apply-filter" width="5%">#</th>
                    <th scope="col" class="apply-filter" width="15%">Process</th>
                    <th scope="col" class="apply-filter" width="20%">Employee Name</th>
                    <th scope="col" class="apply-filter" width="10%">EIN</th>
                    <th scope="col" class="apply-filter" width="15%">Started</th>
                    <th scope="col" class="apply-filter" width="15%">Completed</th>
                    <th scope="col" class="apply-filter" width="10%">Status</th>
                    <th scope="col" class="text-center" width="10%"><strong>Options</strong></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $count = count($adoaListRequestsAgency);
                @endphp
                @if ($count > 0)
                    @foreach ($adoaListRequestsAgency as $request)
                        @if ($request->name != 'Email Notification Sub Process')
                            @php
                                $createdDate = $request->created_at;
                                $newCreatedDate = new DateTime($createdDate);
                                //$newCreatedDate->setTimezone(new DateTimeZone(Auth::user()->timezone));
                                $newCreatedDate->setTimezone(new DateTimeZone('America/Phoenix'));
                                if($request->completed_at != null) {
                                    $completedDate = $request->completed_at;
                                    $newCompletedDate = new DateTime($completedDate);
                                    //$newCompletedDate->setTimezone(new DateTimeZone(Auth::user()->timezone));
                                    $newCompletedDate->setTimezone(new DateTimeZone('America/Phoenix'));
                                    $newCompletedDateFormat = $newCompletedDate->format('m/d/Y h:i:s A');
                                } else {
                                    $newCompletedDateFormat = '';
                                }
                                $data = $request->data;
                                $newData = json_decode($data);
                                if (!is_null($request->custom_properties)) {
                                    $customProperties = $request->custom_properties;
                                    $newCustomProperties = json_decode($customProperties);
                                }
                            @endphp
                            @if ((!empty($newData->EMA_FORM_ACTION) && $newData->EMA_FORM_ACTION != 'DELETE') || (!empty($newData->CON_FORM_ACTION) && $newData->CON_FORM_ACTION != 'DELETE'))
                                @if (!empty($request->file_id) || !is_null($request->file_id))
                                    @if (is_null($newCustomProperties->createdBy))
                                        <tr>
                                            <td class="text-left" style="color: #71A2D4;"><strong>{{ $request->request_id }}</strong></td>
                                            <td class="text-left">{{ $request->name }}</td>
                                            <td class="text-left">
                                                @if ($request->name == 'Remote Work - Terminate Agreement')
                                                    @php
                                                        $dataName = $newCustomProperties->data_name;
                                                        $nameFile = explode('_', $dataName);
                                                    @endphp
                                                    @if (array_key_exists(3, $nameFile) && array_key_exists(4, $nameFile))
                                                        {{ $nameFile[3] }} {{ $nameFile[4] }}
                                                    @endif
                                                @else
                                                    @if (!empty($newData->EMA_EMPLOYEE_FIRST_NAME))
                                                        {{ $newData->EMA_EMPLOYEE_FIRST_NAME }} {{ $newData->EMA_EMPLOYEE_LAST_NAME }}
                                                    @elseif(!empty($newData->CON_EMPLOYEE_FIRST_NAME))
                                                        {{ $newData->CON_EMPLOYEE_FIRST_NAME }} {{ $newData->CON_EMPLOYEE_LAST_NAME }}
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-left">
                                                @if ($request->name == 'Remote Work - Terminate Agreement')
                                                    @if (array_key_exists(5, $nameFile))
                                                        {{ $nameFile[5] }}
                                                    @endif
                                                @else
                                                    @if (!empty($newData->EMA_EMPLOYEE_EIN))
                                                        {{ $newData->EMA_EMPLOYEE_EIN }}
                                                    @elseif (!empty($newData->CON_EMPLOYEE_EIN))
                                                        {{ $newData->CON_EMPLOYEE_EIN }}
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-left">{{ $newCreatedDate->format('m/d/Y h:i:s A') }}</td>
                                            <td class="text-left">{{ $newCompletedDateFormat }}</td>
                                            <td class="text-left">{{ $request->request_status }}</td>
                                            <td class="text-right">
                                                <a href="#"><i class="fas fa-eye" style="color: #71A2D4;" title="View PDF" onclick="viewPdf({{ $request->request_id }}, {{ $request->file_id }});"></i></a>&nbsp;
                                                <a href="#"><i class="fas fa-print" style="color: #71A2D4;" title="Print PDF" onclick="printPdf({{ $request->request_id }}, {{ $request->file_id }});"></i></a>&nbsp;
                                                <a href="/request/{{ $request->request_id }}/files/{{ $request->file_id }}"><i class="fas fa-download" style="color: #71A2D4;" title="Download PDF"></i></a>&nbsp;
                                            </td>
                                        </tr>
                                    @endif
                                @else
                                    <tr>
                                        <td class="text-left" style="color: #71A2D4;"><strong>{{ $request->request_id }}</strong></td>
                                        <td class="text-left">{{ $request->name }}</td>
                                        <td class="text-left">
                                            @if ($request->name != 'Remote Work - Terminate Agreement')
                                                @if (!empty($newData->EMA_EMPLOYEE_FIRST_NAME))
                                                    {{ $newData->EMA_EMPLOYEE_FIRST_NAME }} {{ $newData->EMA_EMPLOYEE_LAST_NAME }}
                                                @elseif(!empty($newData->CON_EMPLOYEE_FIRST_NAME))
                                                    {{ $newData->CON_EMPLOYEE_FIRST_NAME }} {{ $newData->CON_EMPLOYEE_LAST_NAME }}
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-left">
                                            @if ($request->name != 'Remote Work - Terminate Agreement')
                                                @if (!empty($newData->EMA_EMPLOYEE_EIN))
                                                    {{ $newData->EMA_EMPLOYEE_EIN }}
                                                @elseif (!empty($newData->CON_EMPLOYEE_EIN))
                                                    {{ $newData->CON_EMPLOYEE_EIN }}
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-left">{{ $newCreatedDate->format('m/d/Y h:i:s A') }}</td>
                                        <td class="text-left">{{ $newCompletedDateFormat }}</td>
                                        <td class="text-left">{{ $request->request_status }}</td>
                                        <td class="text-right">
                                            <a href="/requests/{{ $request->request_id }}"><i class="fas fa-external-link-square-alt" style="color: #71A2D4;" title="Open request"></i></a>
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        @endif
                    @endforeach
                @endif
          </tbody>
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
</div>
@section('js')
<script>
    window.temp_define = window['define'];
    window['define']  = undefined;
</script>
<script src="https://nightly.datatables.net/js/jquery.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script>
    window['define'] = window.temp_define;
</script>
<script type="text/javascript">
    $(document).ready( function () {
        var table = $('#listRequestsAgency').DataTable({
            "initComplete": function () {
                count = 0;
                this.api().columns().every( function () {
                    if(this.index() != 0 && this.index() != 7) {
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
                        $('.select2').val(null).trigger('change');
                    }
                });
            },
            "order": [[ 0, "desc" ]],
            "pageLength": 25
        });
    });
</script>
@endsection
@endsection
