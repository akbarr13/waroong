<div wire:ignore>
    <div
        x-data="{
            denominations: [1000, 2000, 5000, 10000, 20000, 50000, 100000],
            selected: [],
            totalAmount: $wire.$entangle('data.total_amount'),
            get received() { return this.selected.reduce((sum, i) => sum + i.val, 0); },
            get change() { return Math.max(0, this.received - (parseFloat(this.totalAmount) || 0)); },
            add(val) { this.selected.push({ id: Date.now() + Math.random(), val }); },
            remove(id) { this.selected = this.selected.filter(s => s.id !== id); },
            format(val) { return 'Rp ' + Math.floor(val).toLocaleString('id-ID'); }
        }"
        class="space-y-3"
    >
        {{-- Preset nominal --}}
        <div class="flex flex-wrap gap-2">
            <template x-for="d in denominations" :key="d">
                <button
                    type="button"
                    @click="add(d)"
                    class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium bg-white dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900 hover:border-primary-400 text-gray-700 dark:text-gray-200 transition-colors"
                    x-text="format(d)"
                ></button>
            </template>
        </div>

        {{-- Nominal yang dipilih (chip) --}}
        <div class="flex flex-wrap gap-2" x-show="selected.length > 0">
            <template x-for="item in selected" :key="item.id">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-sm font-medium">
                    <span x-text="format(item.val)"></span>
                    <button
                        type="button"
                        @click="remove(item.id)"
                        class="flex items-center justify-center w-4 h-4 rounded-full hover:bg-red-200 dark:hover:bg-red-800 text-primary-400 hover:text-red-600 transition-colors text-xs font-bold leading-none"
                    >✕</button>
                </span>
            </template>
        </div>

        {{-- Ringkasan --}}
        <div class="space-y-1 border-t border-gray-200 dark:border-gray-700 pt-3" x-show="received > 0">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>Uang Diterima</span>
                <span class="font-medium" x-text="format(received)"></span>
            </div>
            <div
                class="flex justify-between text-base font-bold"
                :class="change >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400'"
            >
                <span>Kembalian</span>
                <span x-text="format(change)"></span>
            </div>
        </div>
    </div>
</div>
