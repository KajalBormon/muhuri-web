<template>
    <div class="modal fade" id="kt_modal_edit_buyer_payment" ref="EditPaymentModalRef" tabindex="-1" aria-hidden="true">
        <!-- Modal content (same as your original code) -->
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <!-- Modal header -->
                <div class="modal-header" id="kt_modal_update_email_header">
                    <h2 class="fw-bold"> {{ isWaiver ? $t("payment.title.editWaiver") : $t("payment.title.edit") }} </h2>
                    <div id="kt_modal_update_payment_close" @click="resetForm" data-bs-dismiss="modal" class="btn btn-icon btn-sm btn-active-icon-primary">
                        <KTIcon icon-name="cross" icon-class="fs-1" />
                    </div>
                </div>

                <!-- Form -->
                <VForm @submit="submit()" :model="formData" ref="formRef">
                    <!-- Modal body -->
                    <div class="modal-body py-10 px-lg-17">
                        <div class="scroll-y me-n7 pe-7" id="kt_modal_update_item_unit" data-kt-scroll="true">
                            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6 d-flex flex-stack flex-grow-1 justify-content-center align-items-center text-center">
                                <!--begin::Content-->
                                <div class="fw-semibold">
                                    <div class="fs-3 text-gray-700"> {{ $t("buyer.label.totalDue") }}: {{ props.totalDue }} </div>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!-- Form fields (same as your original code) -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">{{ $t("payment.label.amount") }}</label>
                                <Field type="text" :placeholder="$t('payment.placeholder.amount')" class="form-control form-control-lg form-control-solid" name="amount" v-model="formData.amount" />
                                <p v-if="errors.amount" class="text-danger"> {{ errors.amount }} </p>
                                <ErrorMessage :errorMessage="formData.errors.amount" />
                            </div>

                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">{{ isWaiver ? $t("payment.label.waiverDate") : $t("payment.label.paymentDate") }}</label>
                                <Field type="date" :placeholder="$t('payment.placeholder.paymentDate')" class="form-control form-control-lg form-control-solid" name="payment_date" v-model="formData.payment_date" />
                                <ErrorMessage :errorMessage="formData.errors.payment_date" />
                            </div>

                            <div class="fv-row mb-7" v-if="!isWaiver">
                                <label class="required fs-6 fw-semibold mb-2">{{ $t("payment.label.paymentMethod") }}</label>
                                <Multiselect :placeholder="$t('payment.placeholder.paymentMethod')" v-model="formData.payment_method" :searchable="true" label="text" trackBy="text" :options="allPaymentMethod" />
                                <ErrorMessage :errorMessage="formData.errors.payment_method" />
                            </div>

                            <div class="fv-row mb-7">
                                <label class="fs-6 fw-semibold mb-2">{{ $t("label.note") }}</label>
                                <Field type="text" :placeholder="$t('placeholder.note')" class="form-control form-control-lg form-control-solid" name="note" v-model="formData.note" />
                                <ErrorMessage :errorMessage="formData.errors.note" />
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="modal-footer flex-center">
                        <div data-bs-dismiss="modal" class="btn btn-light me-3" @click="resetForm">
                            {{ $t("buttonValue.discard") }}
                        </div>
                        <SubmitButton :buttonValue="$t('buttonValue.update')" :id="props.payment?.id" />
                    </div>
                </VForm>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, watch, computed, reactive } from "vue";
import { useForm, router, usePage } from "@inertiajs/vue3";
import { Field, Form as VForm } from "vee-validate";
import Multiselect from "@vueform/multiselect";
import { hideModal } from "@/Core/helpers/Modal";
import ErrorMessage from "@/Components/Message/ErrorMessage.vue";
import SubmitButton from "@/Components/Button/SubmitButton.vue";
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import toastr from "toastr";
import "toastr/toastr.scss";
import i18n from "@/Core/plugins/i18n";
const { t } = i18n.global;

const props = defineProps({
    buyer: Object,
    allPaymentMethod: Array,
    payment: Object,
    totalDue: Number,
});

const formRef = ref<null | HTMLFormElement>(null);
const EditPaymentModalRef = ref<null | HTMLElement>(null);

const formData = useForm({
    amount: props.payment?.amount || "",
    waiver_amount: props.payment?.waiver_amount || "",
    payment_date: props.payment?.payment_date || "",
    payment_method: props.payment?.payment_method || "",
    note: props.payment?.note || "",
});

const isWaiver = computed(() => {
    return props?.payment?.note === "ডিসকাউন্ট";
});

watch(() => props.payment, (newPayment) => {
        if (newPayment) {
            formData.amount = newPayment.amount;
            formData.waiver_amount = newPayment.waiver_amount;
            formData.payment_date = newPayment.payment_date;
            formData.payment_method = newPayment.payment_method;
            formData.note = newPayment.note;
        }
    }, { immediate: true }
);

const resetForm = () => {
    formData.reset();
    formRef.value?.resetForm();
};

const errors = reactive({
    amount: "",
});


const checkTotalDue = () => {
    let valid = true;
    errors.amount = "";

    const amountNum = parseFloat(formData.amount);
    const totalAmount = props.payment?.amount + props?.totalDue;

    if (isWaiver.value) {
        if (typeof props.totalDue !== "number" || props?.totalDue <= 0) {
            errors.amount = t("validation.waiver.zeroOrNegative");
            valid = false;
        } else if (isNaN(amountNum) || amountNum < 0) {
            errors.amount = t("validation.waiver.number");
            valid = false;
        } else if (amountNum > totalAmount) {
            errors.amount = t("validation.waiver.exceed");
            valid = false;
        }
    }
    return valid;
};

const submit = () => {
    if (!checkTotalDue()) return;

    const url = isWaiver.value ?
        route("buyers.payments.updateWaiver", {
            buyer: props.buyer?.id,
            payment: props.payment?.id,
        }) :
        route("buyers.payments.update", {
            buyer: props.buyer?.id,
            payment: props.payment?.id,
        });

    formData.patch(url, {
        onSuccess: () => {
            hideModal(EditPaymentModalRef.value);
            const flash = usePage().props.flash;
            let { success } = flash as any;
            if (flash && success) {
                toastr.success(success);
                success = null;
            }
        },
        onError: (errors) => {
            console.log("Errors:", errors);
        },
    });
};
</script>
