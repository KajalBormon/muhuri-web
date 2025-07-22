<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!-- begin::Invoice 3-->
                <div class="card">
                    <!-- begin::Body-->
                    <div class="card-body py-20">
                        <!-- begin::Wrapper-->
                        <div class="mw-lg-950px mx-auto w-100">
                            <!--begin::Body-->
                            <div class="pb-10" id="invoice-content">
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-column gap-7 gap-md-10">
                                    <!--begin::Message-->
                                    <div class="fw-bold fs-2 text-center">
                                        <CompanyInformation :configuration="props?.configuration" />
                                    </div>
                                    <div class="d-flex justify-content-between" style="margin-bottom: -20px !important;">
                                        <div>
                                            <span class="fw-bold fs-7">{{ $t('label.name') }}:</span> {{ props.buyer?.name }}
                                        </div>
                                        <div>
                                            <span class="fw-bold fs-7">{{ $t('label.address') }}:</span> {{ props.address }}
                                        </div>
                                        <div>
                                            <span class="fw-bold fs-7">{{ $t('label.mobileNumber') }}:</span> {{ props.buyer?.mobile_number }}
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between" style="margin-bottom: -20px !important;">
                                        <div>
                                            <span class="fw-bold fs-7">{{ $t('label.date') }}:</span> {{ props.payment?.payment_date }}
                                        </div>
                                         <div>
                                            <span class="fw-bold fs-7">{{ $t('sale.label.invoiceNumber') }}:</span> {{ props.payment?.invoice_number }}
                                        </div>
                                    </div>
                                    <!--begin::Message-->
                                    <!--begin::Separator-->
                                    <!-- <div class="separator" style="border-color: #9291914f"></div> -->
                                    <!--begin::Separator-->
                                    <!--begin:Order summary-->
                                    <div class="d-flex flex-column" id="invoice-content">
                                        <!--begin::Table-->
                                        <div class="table-responsive mb-9" style="border-color: #9291914f !important">
                                            <table class="table table-bordered fs-6 gy-3 mb-0" style="border-color: #9291914f !important; width: 100%;">
                                                <thead>
                                                    <tr class="fs-6 fw-bold text-muted">
                                                        <th class="pb-2">{{ $t('buyer.label.description') }}</th>
                                                        <th class="pb-2">{{ $t('label.note') }}</th>
                                                        <th class="pb-2 text-end">{{ $t('buyer.label.amount') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="fw-semibold text-gray-600">
                                                    <tr>
                                                        <td class="fw-bold">{{ props.payment?.payment_method }}</td>
                                                        <td>{{ props.payment?.note }}</td>
                                                        <td class="text-end">{{ props.payment?.amount }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td class="fw-bold">{{ $t('payment.label.total') }}</td>
                                                        <td class="text-end fw-bold">{{ props.payment?.amount }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold fs-7">{{ $t('label.totalAmount') }}: {{ props.buyerSummary?.total_transaction }}</td>
                                                        <td class="fw-bold fs-7">{{ $t('label.totalPayment') }}: {{ props.buyerSummary?.total_payment }}</td>
                                                        <td class="fw-bold fs-7 text-end">{{ $t('label.totalDue') }}: {{ props.buyerSummary?.total_due }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!--end::Table-->
                                    </div>

                                    <div class="d-flex justify-content-between mt-8">
                                        <div>
                                            {{ $t('buyer.label.receiverSignature') }}
                                        </div>
                                        <div style="margin-left: 44px;">
                                            {{ $t('buyer.label.producerSignature') }}
                                        </div>
                                        <div>
                                            {{ $t('buyer.label.employeeSignature') }}
                                        </div>
                                    </div>
                                    <!--end:Order summary-->
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Body-->
                            <!-- begin::Footer-->
                            <div class="d-flex flex-stack flex-wrap mt-lg-2 pt-8">
                                <!-- begin::Actions-->
                                <div class="my-1 me-5">
                                    <!-- begin::Pint-->
                                    <button type="button" class="btn btn-primary my-1 me-12" @click="printInvoice">{{ $t('buyer.header.show.buttonValue.printInvoice') }}</button>
                                    <!-- end::Pint-->
                                </div>
                                <!-- end::Actions-->
                            </div>
                            <!-- end::Footer-->
                        </div>
                        <!-- end::Wrapper-->
                    </div>
                    <!-- end::Body-->
                </div>
                <!-- end::Invoice 1-->
            </div>
            <!--end::Content container-->
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { defineProps } from "vue";
import { router } from '@inertiajs/vue3';
import { MenuComponent } from "@/Assets/ts/components";
import CompanyInformation from "@/Components/CompanyInformation.vue";

const props = defineProps({
    buyer: Object,
    payment: Object,
    address: String,
    buyerSummary: Object,
    configuration: Object,
    pageTitle: String,
    breadcrumbs: Array as () => Breadcrumb[],
});

interface Breadcrumb {
    url: string;
    title: string;
}

const printInvoice = () => {
    const invoiceNumber = props.payment?.invoice_number || 'invoice';
    document.title = `Buyer Payment Report ${invoiceNumber}`;
    window.print();
};

</script>
<style>
    @media print {
        @page {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        html,body * {
            visibility: hidden;
        }

        body {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* For second page don't show the heading again */
        thead {
            display: table-row-group !important;
        }

        .card-body, .app-container, .app-content, #kt_app_content {
            margin-top: -15px !important;
            padding-top: -15px !important;
        }

        .card-header{
            margin-top: -3rem;
        }

        #invoice-content, #invoice-content * {
            visibility: visible !important;
        }

        .table {
            page-break-inside: auto;
            border-collapse: collapse !important;
            width: 100% !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .table th,
        .table td {
            border: 0.2px solid #9291914f !important;
            padding: 2px 10px !important;
            text-align: center !important;
        }

        .table:not(.table-bordered) tfoot tr:last-child,
        .table:not(.table-bordered) tbody tr:last-child {
            border-bottom: 0.2px solid #9291914f !important;
        }
    }
</style>
