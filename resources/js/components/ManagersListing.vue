<template>
    <div class="data-table">
        <vuetable :dataManager="dataManager" :sortOrder="sortOrder" :css="css" :api-mode="false"
            @vuetable:pagination-data="onPaginationData" :fields="fields" :data="data" data-path="data"
            pagination-path="meta">
            <template slot="actions" slot-scope="props">
                <div class="actions">
                    <div class="text-right">
                        <b-btn squared variant="outline-primary"
                            @click="onAction('edit-item', props.rowData, props.rowIndex)" v-b-tooltip.hover
                            data-action="Edit" data-toggle="modal" data-target="#sampleModal" title="Edit"><i
                                class="fas fa-edit"></i></b-btn>
                        <b-btn squared variant="outline-primary"
                            @click="onAction('remove-item', props.rowData, props.rowIndex)" v-b-tooltip.hover
                            title="Remove"><i class="fas fa-trash-alt"></i></b-btn>
                    </div>
                </div>
            </template>
        </vuetable>
        <pagination single="Record" plural="Records" :perPageSelectEnabled="true" @changePerPage="changePerPage"
            @vuetable-pagination:change-page="onPageChange" ref="pagination"></pagination>
    </div>
</template>
<script>
import datatableMixin from "./common/mixins/datatable";
export default {
    mixins: [datatableMixin],
    props: ["filter", "userselected"],

    data() {
        return {


            orderBy: "id",
            // Our listing of samples
            sortOrder: [{
                field: "id",
                sortField: "id",
                direction: "asc"
            }],
            fields: [
                {
                    title: "#",
                    name: "data._request.id",
                    sortField: "data._request.id"
                },
                {
                    title: "Process Name",
                    name: "process.name"
                },
                {
                    title: "Employee Name",
                    name: "process.name"
                },
                {
                    title: "Started",
                    name: "process_request.initiated_at"
                },
                {
                    title: "Completed",
                    name: "process_request.completed_at"
                },
                {
                    title: "Current task",
                    name: "element_name"
                },
                {
                    title: "Status",
                    name: "process_request.status"
                }
                /*{
                    name: "__slot:actions",
                    title: ""
                }*/
            ]
        };
    },
    methods: {
        formatStatus(status) {
            status = status.toLowerCase();
            let bubbleColor = {
                active: "text-success",
                inactive: "text-danger",
                draft: "text-warning",
                archived: "text-info"
            };
            return (
                '<i class="fas fa-circle ' +
                bubbleColor[status] +
                ' small"></i> ' +
                status.charAt(0).toUpperCase() +
                status.slice(1)
            );
        },
        onAction(action, data, index) {
            switch (action) {
                case "edit-item":
                    this.$parent.edit(data);
                    break;
            }
        },
        getRwaList() {
            ProcessMaker.apiClient
                .post(
                    'adoa/requests-list/', { 'user_id': this.currentEmployee }
                )
                .then(response => {
                    this.rwaList = response.data;
                    this.showList = true;
                    $('#rwaList').DataTable().destroy();
                })
                .catch(response => {
                    console.log(response);
                });
        },
        fetch() {
            this.loading = true;
            //change method sort by sample
            this.orderBy = this.orderBy === "id" ? "id" : this.orderBy;
            // Load from our api client
            this.userselected = this.userselected || 0;


            /*"tasks?pmql=(user_id=" +
                    this.userselected +
            */
            ProcessMaker.apiClient
                .get(
                    "tasks?include=process,processRequest,processRequest.user,user,data&page=" +
                    this.page +
                    "&per_page=" +
                    this.perPage +
                    "&filter=" +
                    this.filter +
                    "&order_by=" +
                    this.orderBy +
                    "&order_direction=" +
                    this.orderDirection
                )
                .then(response => {
                    this.data = this.transform(response.data);
                    this.loading = false;
                }).finally(() => {
                    this.loadingPage = false;
                });
        }
    }
};
</script>
