import Vue from "vue";
import BootstrapVue from "bootstrap-vue";
import VModal from "vue-js-modal";
import SampleListing from "./components/SamplesListing";

Vue.use(VModal);
Vue.use(BootstrapVue);

new Vue({
    el: "#app-package-zj-adoa",
    data: {
        filter: "",
        sample: {
            id: "",
            name: "",
            status: "ENABLED"
        },
        addError: {
            name: null,
            status: null
        },
        action: "Add"
    },
    components: {SampleListing},
    methods: {
        reload () {
            this.$refs.listing.dataManager([{
                field: "updated_at",
                direction: "desc"
            }]);
        },
        edit (data) {
            this.sample.name = data.name;
            this.sample.status = data.status;
            this.sample.id = data.id;
            this.action = "Edit";
            this.$refs.modal.show();
        },
        validateForm () {
            if (this.sample.name === "" || this.sample.name === null) {
                this.submitted = false;
                this.addError.name = ["The name field is required"];
                return false;
            }
            return true;
        },
        onSubmit (evt) {
            evt.preventDefault();
            this.submitted = true;
            if (this.validateForm()) {
                this.addError.name = null;
                if (this.action === "Add") {
                    ProcessMaker.apiClient.post("admin/package-zj-adoa", {
                        name: this.sample.name,
                        status: this.sample.status
                    })
                        .then((response) => {
                            this.reload();
                            ProcessMaker.alert("Sample successfully added ", "success");
                            this.sample.name = "";
                            this.sample.status = "ENABLED";
                        })
                        .catch((error) => {
                            if (error.response.status === 422) {
                                this.addError = error.response.data.errors;
                            }
                        })
                        .finally(() => {
                            this.submitted = false;
                            this.$refs.modal.hide();
                        });
                } else {
                    ProcessMaker.apiClient.patch(`admin/package-zj-adoa/${this.sample.id}`, {
                        name: this.sample.name,
                        status: this.sample.status
                    })
                        .then((response) => {
                            this.reload();
                            ProcessMaker.alert("Sample successfully updated ", "success");
                            this.sample.name = "";
                            this.sample.status = "ENABLED";
                        })
                        .catch((error) => {
                            if (error.response.status === 422) {
                                this.addError = error.response.data.errors;
                            }
                        })
                        .finally(() => {
                            this.submitted = false;
                            this.$refs.modal.hide();
                            this.action = "create";
                        });
                }
            }
        },
        clearForm () {
            this.action = "Add";
            this.id = "";
            this.addError.name = null;
            this.sample.name = "";
        }
    }
});
