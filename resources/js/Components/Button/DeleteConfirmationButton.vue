<template>
    <button
        v-if="showIconButton"
        @click="showDeleteConfirmation"
        class="btn btn-icon btn-flex btn-active-light-danger w-30px h-30px"
        data-bs-toggle="tooltip"
        :title="$t('tooltip.title.delete')"
        type="button"
    >
        <KTIcon icon-name="trash" :icon-class="'text-danger ' + (iconClass ? iconClass : 'fs-3')"
        />
    </button>

    <button
        v-else
        @click="showDeleteConfirmation"
        class="btn btn-primary me-3"
        data-bs-toggle="tooltip"
        :title="$t('tooltip.title.delete')"
    >
        {{ buttonText || 'Delete' }}
    </button>
</template>


<script setup lang="ts">
import { defineProps } from 'vue';
import Swal from 'sweetalert2';
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import i18n from '@/Core/plugins/i18n';
import { router } from '@inertiajs/vue3';

const { t } = i18n.global;

const props = defineProps({
    obj: Object,
    confirmRoute: String,
    roleId: Number,
    attendanceId: Number,
    employee: Object,
    iconClass: String,
    payment: Object,
    buyerId: Number,
    supplierId: Number,
    title: String,
    messageTitle: String,
    showIcon: {
        type: Boolean,
        default: true,
    },
    saleId: Number,
    saleItem: Object,
    purchaseId: Number,
    purchaseItem: Object,
    buttonText: String,
});

const showIconButton = props.showIcon ?? true;

const showDeleteConfirmation = async () => {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger',
        },
        buttonsStyling: false,
    });

    const result = await swalWithBootstrapButtons.fire({
        title: `${t('confirmation.remove.others', { messageTitle: props?.messageTitle })}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `${t('buttonValue.confirm')}!`,
        cancelButtonText: `${t('buttonValue.cancel')}`,
        reverseButtons: true,
    });


    if (result.isConfirmed) {
        Swal.fire({
            title: `${t('confirmation.wait')}`,
            timer: 1000,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        if (props?.roleId) {
            router.visit(route(props.confirmRoute as string, { role: props?.roleId, user: props.obj?.id }), {
                method: 'delete',
                data: {},
                replace: true,
                preserveScroll: true,
            });
        } else if (props?.attendanceId) {
            router.visit(route(props.confirmRoute as string, [props.employee?.id, props?.attendanceId]), {
                method: 'delete',
                data: {},
                replace: true,
                preserveScroll: true,
            });
        } else if (props?.buyerId) {
            router.visit(route(props.confirmRoute as string, [props.buyerId, props.payment?.id]), {
                method: 'delete',
                data: {},
                replace: true,
                preserveScroll: true,
            });
        } else if (props?.supplierId) {
            router.visit(route(props.confirmRoute as string, [props.supplierId, props.payment?.id]), {
                method: 'delete',
                data: {},
                replace: true,
                preserveScroll: true,
            });
        } else if (props?.saleId && props?.saleItem?.id){
            console.log('sale', props?.saleId, props?.saleItem?.id);
            router.visit(route(props.confirmRoute as string, [props.saleId, props.saleItem?.id]), {
                method: 'delete',
                data: {},
                replace: true,
                preserveScroll: true,
            });
        } else if (props?.purchaseId && props?.purchaseItem?.id){
            router.visit(route(props.confirmRoute as string, [props.purchaseId, props.purchaseItem?.id]), {
                method: 'delete',
                data: {},
                replace: true,
                preserveScroll: true,
            });
        }
        else {
            router.visit(route(props.confirmRoute as string, props.obj?.id), {
                method: 'delete',
                data: {},
                replace: true,
                preserveScroll: true,
            });
        }
    }
};
</script>

