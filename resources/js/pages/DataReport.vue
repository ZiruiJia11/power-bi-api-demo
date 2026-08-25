<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle2,
    Clipboard,
    Database,
    Download,
    ExternalLink,
    FileBarChart,
    FileCog,
    KeyRound,
    Link2,
    ShieldCheck,
} from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { data_report } from '@/routes';

const props = defineProps<{
    odataUrl: string;
    demoCredentials: Array<{
        tenant: string;
        name: string;
        username: string;
        password: string;
    }>;
    datasets: string[];
    connectionDownloadUrl: string;
    templateDownloadUrl: string;
    templateAvailable: boolean;
}>();
const copiedValue = ref<string | null>(null);

const copyValue = async (label: string, value: string): Promise<void> => {
    await navigator.clipboard.writeText(value);
    copiedValue.value = label;
    window.setTimeout(() => {
        copiedValue.value = null;
    }, 1800);
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Power BI Integration', href: data_report() }],
    },
});
</script>

<template>
    <Head title="Power BI Integration" />
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 lg:p-6">
        <div class="flex flex-col gap-2">
            <div
                class="flex items-center gap-2 text-sm font-medium text-emerald-700 dark:text-emerald-300"
            >
                <ShieldCheck class="size-4" /> Customer-facing data integration
                demo
            </div>
            <div
                class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between"
            >
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-semibold tracking-tight">
                        Connect Power BI to Dharta
                    </h1>
                    <p class="max-w-3xl text-sm text-muted-foreground">
                        Use one secure OData feed to discover Dharta datasets
                        and build reports in your own Power BI workspace.
                    </p>
                </div>
                <Badge
                    variant="outline"
                    class="w-fit gap-1.5 border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200"
                >
                    <span class="size-1.5 rounded-full bg-emerald-500" /> Feed
                    ready
                </Badge>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
            <Card class="border-blue-200 dark:border-blue-900">
                <CardHeader>
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="rounded-lg bg-blue-100 p-2 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                            >
                                <Download class="size-5" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <CardTitle>Connect to Power BI</CardTitle>
                                    <Badge>Fastest</Badge>
                                </div>
                                <p class="text-sm text-muted-foreground">
                                    Open a preconfigured Dharta OData connection
                                    in Power BI Desktop.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <ol
                        class="flex flex-col gap-2 text-sm text-muted-foreground"
                    >
                        <li>
                            1. Download the small
                            <code class="rounded bg-muted px-1.5 py-0.5"
                                >.pbids</code
                            >
                            file.
                        </li>
                        <li>2. Open it with Power BI Desktop.</li>
                        <li>
                            3. Choose Basic authentication and enter the Dharta
                            credential.
                        </li>
                        <li>4. Select the Dharta datasets to load.</li>
                    </ol>
                    <Button as-child class="w-full sm:w-fit">
                        <a :href="props.connectionDownloadUrl" download>
                            <Download class="size-4" /> Download connection file
                        </a>
                    </Button>
                </CardContent>
            </Card>

            <Card class="border-emerald-200 dark:border-emerald-900">
                <CardHeader>
                    <div class="flex items-start gap-3">
                        <div
                            class="rounded-lg bg-emerald-100 p-2 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                        >
                            <Link2 class="size-5" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <CardTitle>Connect with OData URL</CardTitle>
                                <Badge variant="secondary">Browser-safe</Badge>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Copy the feed URL without downloading any
                                connection file.
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div
                        class="rounded-lg border bg-muted/30 p-3 font-mono text-xs break-all"
                    >
                        {{ props.odataUrl }}
                    </div>
                    <ol
                        class="flex flex-col gap-2 text-sm text-muted-foreground"
                    >
                        <li>
                            1. In Power BI Desktop, select
                            <strong class="font-medium text-foreground"
                                >Get data → OData Feed</strong
                            >.
                        </li>
                        <li>
                            2. Paste the copied URL and select
                            <strong class="font-medium text-foreground"
                                >Connect</strong
                            >.
                        </li>
                        <li>
                            3. Choose Basic authentication and enter the Dharta
                            credential.
                        </li>
                        <li>
                            4. Choose the Dharta datasets and select
                            <strong class="font-medium text-foreground"
                                >Load</strong
                            >.
                        </li>
                    </ol>
                    <Button
                        type="button"
                        variant="outline"
                        class="w-full sm:w-fit"
                        @click="copyValue('quick-url', props.odataUrl)"
                    >
                        <Check
                            v-if="copiedValue === 'quick-url'"
                            class="size-4 text-emerald-600"
                        />
                        <Clipboard v-else class="size-4" />
                        {{
                            copiedValue === 'quick-url'
                                ? 'URL copied'
                                : 'Copy OData URL'
                        }}
                    </Button>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <div class="flex items-start gap-3">
                        <div
                            class="rounded-lg bg-violet-100 p-2 text-violet-700 dark:bg-violet-950 dark:text-violet-300"
                        >
                            <FileCog class="size-5" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <CardTitle>Download report template</CardTitle>
                                <Badge
                                    v-if="props.templateAvailable"
                                    variant="secondary"
                                    >Ready</Badge
                                >
                                <Badge v-else variant="outline"
                                    >Template file needed</Badge
                                >
                            </div>
                            <p class="text-sm text-muted-foreground">
                                Start with tables, relationships, measures, and
                                sample report pages already configured.
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <p class="text-sm leading-6 text-muted-foreground">
                        Power BI Desktop must export the real
                        <code class="rounded bg-muted px-1.5 py-0.5"
                            >.pbit</code
                        >
                        package. Once added to Dharta, this button becomes
                        available automatically.
                    </p>
                    <Button
                        v-if="props.templateAvailable"
                        as-child
                        variant="outline"
                        class="w-full sm:w-fit"
                    >
                        <a :href="props.templateDownloadUrl" download>
                            <Download class="size-4" /> Download report template
                        </a>
                    </Button>
                    <Button
                        v-else
                        type="button"
                        variant="outline"
                        class="w-full sm:w-fit"
                        disabled
                    >
                        <FileCog class="size-4" /> Awaiting exported template
                    </Button>
                </CardContent>
            </Card>
        </div>

        <div
            class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(20rem,0.6fr)]"
        >
            <Card>
                <CardHeader class="border-b bg-muted/30">
                    <div class="flex items-start gap-3">
                        <div class="rounded-lg border bg-background p-2">
                            <Link2 class="size-5 text-blue-600" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <CardTitle>Connection details</CardTitle>
                            <p class="text-sm text-muted-foreground">
                                Paste this feed URL into Power BI Desktop, then
                                choose Basic authentication and enter one of the
                                demo credentials below.
                            </p>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="flex flex-col gap-5 pt-6">
                    <div class="flex flex-col gap-2">
                        <label for="odata-url" class="text-sm font-medium"
                            >OData feed URL</label
                        >
                        <div class="flex gap-2">
                            <Input
                                id="odata-url"
                                :model-value="props.odataUrl"
                                readonly
                                class="font-mono text-xs"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                aria-label="Copy OData feed URL"
                                @click="copyValue('url', props.odataUrl)"
                            >
                                <Check
                                    v-if="copiedValue === 'url'"
                                    class="size-4 text-emerald-600"
                                /><Clipboard v-else class="size-4" />{{
                                    copiedValue === 'url' ? 'Copied' : 'Copy'
                                }}
                            </Button>
                        </div>
                    </div>
                    <div
                        v-if="props.demoCredentials.length"
                        class="flex flex-col gap-3"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-medium">Demo credentials</p>
                            <Badge variant="secondary">Development only</Badge>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <div
                                v-for="credential in props.demoCredentials"
                                :key="credential.tenant"
                                class="flex flex-col gap-3 rounded-lg border bg-muted/20 p-4"
                            >
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <p class="text-sm font-medium">
                                        {{ credential.name }}
                                    </p>
                                    <Badge variant="outline">{{
                                        credential.tenant
                                    }}</Badge>
                                </div>
                                <dl
                                    class="grid grid-cols-[5rem_1fr] gap-x-3 gap-y-2 text-xs"
                                >
                                    <dt class="text-muted-foreground">
                                        Username
                                    </dt>
                                    <dd class="font-mono">
                                        {{ credential.username }}
                                    </dd>
                                    <dt class="text-muted-foreground">
                                        Password
                                    </dt>
                                    <dd class="font-mono">
                                        {{ credential.password }}
                                    </dd>
                                </dl>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="
                                        copyValue(
                                            `credential-${credential.tenant}`,
                                            `${credential.username}:${credential.password}`,
                                        )
                                    "
                                >
                                    <Check
                                        v-if="
                                            copiedValue ===
                                            `credential-${credential.tenant}`
                                        "
                                        class="size-4 text-emerald-600"
                                    />
                                    <Clipboard v-else class="size-4" />
                                    {{
                                        copiedValue ===
                                        `credential-${credential.tenant}`
                                            ? 'Copied'
                                            : 'Copy credential'
                                    }}
                                </Button>
                            </div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Connect once with Acme, then clear the Power BI data
                            source permissions and reconnect with BuildCo to
                            verify that each credential returns different data.
                        </p>
                    </div>
                    <div class="rounded-lg border bg-muted/20 p-4">
                        <div class="flex items-start gap-3">
                            <KeyRound
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                            />
                            <div class="flex flex-col gap-1">
                                <p class="text-sm font-medium">
                                    Secure by default
                                </p>
                                <p
                                    class="text-xs leading-5 text-muted-foreground"
                                >
                                    The endpoint now requires a tenant-specific
                                    credential. The PBIDS file contains only the
                                    URL and never contains a password.
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="border-b bg-muted/30"
                    ><div class="flex items-start gap-3">
                        <div class="rounded-lg border bg-background p-2">
                            <Database class="size-5 text-violet-600" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <CardTitle>Available datasets</CardTitle>
                            <p class="text-sm text-muted-foreground">
                                Automatically discovered from OData metadata.
                            </p>
                        </div>
                    </div></CardHeader
                >
                <CardContent class="pt-4">
                    <ul class="divide-y">
                        <li
                            v-for="dataset in props.datasets"
                            :key="dataset"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div class="flex items-center gap-2.5">
                                <FileBarChart
                                    class="size-4 text-muted-foreground"
                                /><span class="text-sm font-medium">{{
                                    dataset
                                }}</span>
                            </div>
                            <CheckCircle2 class="size-4 text-emerald-600" />
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader class="border-b bg-muted/30"
                ><CardTitle
                    >Connect from Power BI Desktop</CardTitle
                ></CardHeader
            >
            <CardContent class="pt-6">
                <ol class="grid gap-4 md:grid-cols-3">
                    <li
                        v-for="(step, index) in [
                            [
                                'Open OData Feed',
                                'Select Get data → OData Feed in Power BI Desktop.',
                            ],
                            [
                                'Connect securely',
                                'Enter the feed URL and authenticate with your Dharta integration token.',
                            ],
                            [
                                'Choose datasets',
                                'Select the tables you need, load them, and start building reports.',
                            ],
                        ]"
                        :key="step[0]"
                        class="flex gap-3"
                    >
                        <span
                            class="grid size-7 shrink-0 place-items-center rounded-full bg-blue-600 text-xs font-semibold text-white"
                            >{{ index + 1 }}</span
                        >
                        <div class="flex flex-col gap-1">
                            <p class="text-sm font-medium">{{ step[0] }}</p>
                            <p class="text-xs leading-5 text-muted-foreground">
                                {{ step[1] }}
                            </p>
                        </div>
                    </li>
                </ol>
                <div
                    class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-lg border bg-muted/20 p-4"
                >
                    <p class="text-sm text-muted-foreground">
                        Power BI Embedded is not required—customers own and
                        publish their reports.
                    </p>
                    <Button as-child variant="outline"
                        ><a
                            href="https://learn.microsoft.com/en-us/power-query/connectors/odata-feed"
                            target="_blank"
                            rel="noreferrer"
                            >OData connector guide
                            <ExternalLink class="size-4" /></a
                    ></Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
