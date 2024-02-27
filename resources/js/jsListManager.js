import Vue from "vue";
import BootstrapVue from "bootstrap-vue";
import VModal from "vue-js-modal";
import Multiselect from "vue-multiselect"
import ManagerListing from "./components/ManagersListing";


Vue.use(VModal);
Vue.use(BootstrapVue);


new Vue({
    el: "#app-adoa",
    data: {
        filter: "",
        userselected: "",
        currentEmployee: 0,
        currentEmployeeData: {},
        addError: {
            name: null,
            status: null
        },
        action: "Add",
        employeesList: [],
        employeesList2: [],
        employeesList3: [],
        employeesList4: [],
        employeesList5: [],
        employeeSelectedParent: {
            "meta": { "manager": "" }
        }, employeeSelectedParent2: {
            "meta": { "manager": "" }
        },
        employeeSelectedParent3: {
            "meta": { "manager": "" }
        },
        employeeSelectedParent4: {
            "meta": { "manager": "" }
        },
        employeeSelectedParent5: {
            "meta": { "manager": "" }
        },
        showLevels: {
            select1: false,
            select2: false,
            select3: false,
            select4: false,
            select5: false
        },
        spinners: {
            select1: false,
            select2: false,
            select3: false,
            select4: false,
            select5: false
        }
    },
    components: { Multiselect, ManagerListing },
    methods: {
        getEmployees() {
            this.spinners.select1 = true;
            ProcessMaker.apiClient.get("/adoa/employee-list-data/" + USER_ID).then({
            }).then((response) => {
                this.employeesList = response.data;
            }).catch((error) => {
                console.log('ERROR: ', error);
            }).finally(() => {
                this.loadingPage = false;
                this.spinners.select1 = false;
            });
        },
        updateCurrentEmployee(currentEmployee, level) {
            this.currentEmployee = currentEmployee.id;
            this.currentEmployeeData = currentEmployee;
            switch (level) {
                case 1:
                    this.spinners.select2 = true;
                    this.showLevels.select2 = false;
                    this.cleanLevel1();
                    break;
                case 2:
                    this.spinners.select3 = true;
                    this.showLevels.select3 = false;
                    this.cleanLevel2();
                    break;
                case 3:
                    this.spinners.select4 = true;
                    this.showLevels.select4 = false;
                    this.cleanLevel3();
                    break;
                case 4:
                    this.spinners.select5 = true;
                    this.showLevels.select5 = false;
                    this.cleanLevel4();
                    break;
            }
            ProcessMaker.apiClient.get("/adoa/employee-list-data/" + this.currentEmployee).then({
            }).then((response) => {
                switch (level) {
                    case 1:
                        if (this.employeeSelectedParent.meta.manager == "Y") {
                            this.employeesList2 = response.data;
                            this.showLevels.select2 = true;
                        }
                        break;
                    case 2:
                        if (this.employeeSelectedParent2.meta.manager == "Y") {
                            this.employeesList3 = response.data;
                            this.showLevels.select3 = true;
                        }
                        break;
                    case 3:
                        if (this.employeeSelectedParent3.meta.manager == "Y") {
                            this.employeesList4 = response.data;
                            this.showLevels.select4 = true;
                        }
                        break;
                    case 4:
                        if (this.employeeSelectedParent4.meta.manager == "Y") {
                            this.employeesList5 = response.data;
                            this.showLevels.select5 = true;
                        }
                        break;
                }
            }).catch((error) => {
                console.log('ERROR: ', error);
            }).finally(() => {
                this.loadingPage = false;
                this.spinners.select2 = false;
                this.spinners.select3 = false;
                this.spinners.select4 = false;
                this.spinners.select5 = false;
            });
        },
        unselected(level) {
            switch (level) {
                case 1:
                    this.currentEmployee = 0;
                    this.currentEmployeeData = {};
                    this.cleanLevel1();
                    break;
                case 2:
                    this.currentEmployee = this.employeeSelectedParent.id;
                    this.currentEmployeeData = this.employeeSelectedParent;
                    this.cleanLevel2();
                    break;
                case 3:
                    this.currentEmployee = this.employeeSelectedParent2.id;
                    this.currentEmployeeData = this.employeeSelectedParent2;
                    this.cleanLevel3();
                    break;
                case 4:
                    this.currentEmployee = this.employeeSelectedParent3.id;
                    this.currentEmployeeData = this.employeeSelectedParent3;
                    this.cleanLevel4();
                    break;
                case 5:
                    this.currentEmployee = this.employeeSelectedParent4.id;
                    this.currentEmployeeData = this.employeeSelectedParent4;
                    break;
            }
        },
        reload() {
            this.$refs.listing.dataManager([{
                field: "id",
                direction: "desc"
            }]);
        },
        cleanLevel1() {
            this.employeesList2 = [];
            this.employeesList3 = [];
            this.employeesList4 = [];
            this.employeesList5 = [];
            this.showLevels.select2 = false;
            this.showLevels.select3 = false;
            this.showLevels.select4 = false;
            this.showLevels.select5 = false;


            this.employeeSelectedParent2 = {
                "meta": { "manager": "" }
            };
            this.employeeSelectedParent3 = {
                "meta": { "manager": "" }
            };
            this.employeeSelectedParent4 = {
                "meta": { "manager": "" }
            };
            this.employeeSelectedParent5 = {
                "meta": { "manager": "" }
            };
        },
        cleanLevel2() {
            this.employeesList3 = [];
            this.employeesList4 = [];
            this.employeesList5 = [];
            this.showLevels.select3 = false;
            this.showLevels.select4 = false;
            this.showLevels.select5 = false;
            this.employeeSelectedParent3 = {
                "meta": { "manager": "" }
            };
            this.employeeSelectedParent4 = {
                "meta": { "manager": "" }
            };
            this.employeeSelectedParent5 = {
                "meta": { "manager": "" }
            };
        },
        cleanLevel3() {
            this.employeesList4 = [];
            this.employeesList5 = [];
            this.showLevels.select4 = false;
            this.showLevels.select5 = false;
            this.employeeSelectedParent4 = {
                "meta": { "manager": "" }
            };
            this.employeeSelectedParent5 = {
                "meta": { "manager": "" }
            };
        }, cleanLevel4() {
            this.employeesList5 = [];
            this.showLevels.select5 = false;
            this.employeeSelectedParent5 = {
                "meta": { "manager": "" }
            };
        },
        getRequestsList() {
            var table = $('#listRequests').DataTable({
                "initComplete": function () {
                    count = 0;
                    this.api().columns().every(function () {
                        if (this.index() != 0 && this.index() != 9) {
                            var title = titleTable[this.index()];
                            //replace spaces with dashes
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

                            $('#listRequests .select2').val(null).trigger('change');
                        }
                    });
                },
                "order": [[0, "desc"]],
                "pageLength": 25,
                "destroy": true,
                "ajax": {
                    "url": '/api/1.0/adoa/requests-list/' + this.currentEmployee,
                    "type": "GET",
                    headers: {
                        "X-CSRF-TOKEN": token,
                        "Content-Type": "application/json",
                    }
                },

                "columns": [
                    { data: 'request_id', className: 'text-left' },
                    { data: 'process_name', className: 'text-left' },
                    { data: 'employee_name', className: 'text-left' },
                    { data: 'employee_ein', className: 'text-left' },
                    { data: 'started', className: 'text-left' },
                    { data: 'completed', className: 'text-left' },
                    { data: 'current_task', className: 'text-left' },
                    { data: 'current_user', className: 'text-left' },
                    { data: 'status', className: 'text-left' },
                    { data: 'options', className: 'text-right' }
                ],
                'language': {
                    "loadingRecords": "<div class='lds-ring'><div></div><div></div><div></div><div></div></div><br>Please wait, we are getting your information"
                }
            });
        }
    },
    mounted() {
        this.getEmployees();
    },
});