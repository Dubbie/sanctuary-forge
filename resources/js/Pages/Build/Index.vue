<script setup>
import AppAlert from "@/Components/AppAlert.vue";
import AppButton from "@/Components/AppButton.vue";
import BuildCard from "@/Components/BuildCard.vue";
import SelectInput from "@/Components/SelectInput.vue";
import TextInput from "@/Components/TextInput.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { IconSearch } from "@tabler/icons-vue";
import { inject, onMounted, ref, watch } from "vue";
import BuildsLoading from "./Partials/BuildsLoading.vue";
import EmptyState from "@/Components/EmptyState.vue";

const builds = ref([]);
const loading = ref(true);
const emitter = inject("emitter");
let timer = null;

const filter = useForm({
    search: "",
    class_id: null,
});

const classOptions = [{ value: null, label: "Any class" }].concat(
    usePage().props.classes.map((c) => {
        return { value: c.id.toString(), label: c.name };
    })
);

const getBuilds = () => {
    loading.value = true;
    axios
        .get(route("api.build.fetch"), {
            params: filter.data(),
        })
        .then((response) => {
            builds.value = response.data.data;
        })
        .catch((error) => {
            console.log(error);
        })
        .finally(() => {
            loading.value = false;
        });
};

const handleInput = () => {
    if (timer) {
        clearTimeout(timer);
    }

    timer = setTimeout(() => {
        getBuilds();
    }, 300);
};

onMounted(() => {
    getBuilds();
});

watch(
    () => filter,
    () => {
        handleInput();
    },
    { deep: true }
);
</script>

<template>
    <Head title="Guides" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">
                Guides
            </h2>
        </template>

        <div class="mb-12">
            <p class="font-semibold text-sm mb-1">Filter</p>

            <div class="grid grid-cols-3 gap-6">
                <div class="col-span-2">
                    <TextInput
                        v-model="filter.search"
                        class="w-full text-sm"
                        aria-placeholder="Search"
                        placeholder="Start typing to search..."
                    />
                </div>

                <div class="col-span-1">
                    <SelectInput
                        v-model="filter.class_id"
                        :options="classOptions"
                    />
                </div>
            </div>
        </div>

        <div>
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
                mode="out-in"
            >
                <BuildsLoading v-if="loading" />
                <div v-else>
                    <div
                        v-if="builds.length > 0"
                        class="grid grid-cols-4 gap-6"
                    >
                        <BuildCard
                            :href="route('build.show', build)"
                            v-for="build in builds"
                            :key="build.id"
                            :build="build"
                        />
                    </div>
                    <div v-else>
                        <template v-if="filter.isDirty">
                            <EmptyState
                                title="No guides found"
                                description="Your search did not match any guides. Please try again."
                            >
                                <template #action>
                                    <AppButton outline @click="filter.reset()"
                                        >Clear search</AppButton
                                    >
                                </template>
                            </EmptyState>
                        </template>
                        <template v-else>
                            <EmptyState
                                title="No guides found"
                                description="It seems like we don't have any guides yet."
                            >
                                <template #action>
                                    <AppButton
                                        color="blue"
                                        @click="
                                            emitter.emit('open-new-guide-modal')
                                        "
                                        >Create Guide</AppButton
                                    >
                                </template>
                            </EmptyState>
                        </template>
                    </div>
                </div>
            </transition>
        </div>
    </AuthenticatedLayout>
</template>
