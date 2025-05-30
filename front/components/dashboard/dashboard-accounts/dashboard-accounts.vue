<template>
  <van-cell-group inset>
    <div class="flex-center-vertical">
      <div class="van-cell-group-title">{{ $t('dashboard.account_total') }}:</div>
    </div>

    <van-grid :column-num="2">
      <van-grid-item v-for="account in visibleDashboardAccounts" :key="account.id" @click="onShowActionSheet(account)">
        <template #icon>
          <app-icon :icon="Account.getIcon(account) ?? TablerIconConstants.account" :size="24" />
        </template>

        <template #text>
          <div class="display-flex flex-column align-items-center">
            <div class="font-400 text-size-12 text-center text-muted">{{ Account.getDisplayName(account) }}</div>
            <div class="font-700 text-size-12 text-center">{{ getAccountAmount(account) }}</div>
          </div>
        </template>
      </van-grid-item>
    </van-grid>

    <div v-if="hasHiddenAccounts" class="flex-center">
      <div @click="toggleHiddenAccounts" class="p-5 m-5 button-link">
        {{ showHiddenAccounts ? 'View less...' : 'View more...' }}
        <component :is="showHiddenAccounts ? IconLibraryMinus : IconLibraryPlus" :size="20" :stroke="1.7" />
      </div>
    </div>

    <div class="flex-center text-size-13 m-10 flex-wrap">
      <div class="flex-center text-size-13 me-1">
        <icon-cash class="text-muted" :size="24" :stroke="1.5" />
        <span class="font-400 text-muted">{{ $t('total')}}: </span>
      </div>

      <span v-for="(totalValue, totalCurrency) in dataStore.dashboardAccountsTotalByCurrency" class="font-700 ms-1 mx-1 app-select-option-tag">
        {{ formatNumberForDashboard(totalValue) }} {{ totalCurrency }}
      </span>
    </div>

    <div v-if="hasMultipleCurrencies" class="flex-center text-size-13 mb-3 gap-1">
      <span class="font-700">~{{ accountTotal }} {{ Currency.getCode(dataStore.dashboardCurrency)  }}</span>
    </div>
  </van-cell-group>
</template>

<script setup>
import TablerIconConstants from '~/constants/TablerIconConstants.js'
import Account from '~/models/Account.js'
import RouteConstants from '~/constants/RouteConstants.js'
import { IconCash, IconLibraryPlus, IconLibraryMinus } from '@tabler/icons-vue'
import { formatNumberForDashboard } from '~/utils/NumberUtils.js'
import { useActionSheet } from '~/composables/useActionSheet.js'
import Currency from '../../../models/Currency.js'

const profileStore = useProfileStore()
const dataStore = useDataStore()

const showHiddenAccounts = ref(false)
const toggleHiddenAccounts = () => {
  showHiddenAccounts.value = !showHiddenAccounts.value
}

const visibleDashboardAccounts = computed(() => {
  const sortedAccounts = dataStore.dashboardAccounts.sort((a, b) => Account.getIsVisibleOnDashboard(b) - Account.getIsVisibleOnDashboard(a))
  return showHiddenAccounts.value ? sortedAccounts : sortedAccounts.filter((account) => Account.getIsVisibleOnDashboard(account))
})

const hasHiddenAccounts = computed(() => dataStore.dashboardAccounts.some((account) => !Account.getIsVisibleOnDashboard(account)))

const accountTotal = computed(() => {
  return formatNumberForDashboard(dataStore.dashboardAccountsEstimatedTotal)
})

const getAccountAmount = (account) => {
  return `${formatNumberForDashboard(Account.getBalance(account))} ${Account.getCurrencySymbol(account)}`
}

const hasMultipleCurrencies = computed(() => dataStore.dashboardAccountsCurrencyList.length > 1)

const actionSheet = useActionSheet()
const onShowActionSheet = (account) => {
  actionSheet.show([
    { name: 'Edit account', callback: () => onGoToAccount(account) },
    { name: 'Show transactions', callback: () => onGoToTransactions(account) },
  ])
}

const onGoToTransactions = async (account) => {
  if (account) {
    let filters = TransactionFilterUtils.filters.account.toUrl([account])
    await navigateTo(`${RouteConstants.ROUTE_TRANSACTION_LIST}?${filters}`)
  }
}

const onGoToAccount = async (account) => {
  if (account) {
    await navigateTo(`${RouteConstants.ROUTE_ACCOUNT_ID}/${account.id}`)
  }
}
</script>
