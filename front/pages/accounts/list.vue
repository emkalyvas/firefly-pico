<template>
  <div :class="formClass">
    <app-top-toolbar>
      <template #right>
        <app-button-list-add @click="onAdd" />
      </template>
    </app-top-toolbar>

    <empty-list v-if="isEmpty" />

    <van-pull-refresh v-model="isRefreshing" @refresh="onRefresh">
      <van-list class="p-1" :finished="isFinished" @load="onLoadMore">
        <app-list-search v-if="isSearchVisible && list.length > 0" v-model="search" />

        <van-collapse v-model="visibleAccountTypes">
          <van-collapse-item v-for="{ accounts, typeName } in accountsGroupList" :title="typeName" :name="typeName">
            <account-list-item v-for="item in accounts" :key="item.id" :value="item" @onEdit="onEdit" @onDelete="onDelete" />
          </van-collapse-item>
        </van-collapse>
      </van-list>
    </van-pull-refresh>
  </div>
</template>

<script setup>
import RouteConstants from '~/constants/RouteConstants'
import { useDataStore } from '~/stores/dataStore'
import { useList } from '~/composables/useList'
import Account from '~/models/Account'
import { useToolbar } from '~/composables/useToolbar'
import { ref } from 'vue'
import { get } from 'lodash'

import TablerIconConstants from '~/constants/TablerIconConstants'
import AppListSearch from '~/components/ui-kit/theme/app-list-search.vue'
import { animateSwipeList } from '~/utils/AnimationUtils.js'

let dataStore = useDataStore()

const onEvent = (event, payload) => {
  if (event === 'onPostDelete') {
    dataStore.accountList = dataStore.accountList.filter((item) => parseInt(item.id) !== parseInt(payload.id))
  }
}

const { isLoading, isFinished, isRefreshing, list, isEmpty, onAdd, onEdit, onDelete } = useList({
  title: 'Accounts list',
  routeList: RouteConstants.ROUTE_ACCOUNT_LIST,
  routeForm: RouteConstants.ROUTE_ACCOUNT_ID,
  model: new Account(),
  onEvent: onEvent,
})

const search = ref('')
const isSearchVisible = ref(true)

const visibleAccountTypes = ref(Object.values(Account.types).map((account) => account.name))

const filteredList = computed(() => {
  if (search.value.length === 0) {
    return list.value
  }
  return list.value.filter((item) => Account.getDisplayName(item).toUpperCase().indexOf(search.value.toUpperCase()) !== -1)
})

const accountsGroupList = computed(() => {
  const groupedAccounts = filteredList.value.reduce((result, account) => {
    const typeName = get(Account.getType(account), 'name')
    result[typeName] = [...(result[typeName] ?? []), account]
    return result
  }, {})

  return Object.keys(groupedAccounts)
    .sort()
    .map((typeName) => ({
      typeName,
      accounts: groupedAccounts[typeName],
    }))
})

const formClass = computed(() => ({
  'app-form': true,
  empty: isEmpty.value,
}))

const onRefresh = async () => {
  isLoading.value = true
  isRefreshing.value = true

  const dataStore = useDataStore()
  await dataStore.fetchAccounts()

  isLoading.value = false
  isRefreshing.value = false
  onLoadMore()
}

const onLoadMore = () => {
  const dataStore = useDataStore()
  list.value = dataStore.accountList
}

// const onClickBack = async () => {
//   await navigateTo(RouteConstants.ROUTE_EXTRAS)
// }

const toolbar = useToolbar()
const { t } = useI18n()
toolbar.init({
  title: t('accounts'),
  titleIcon: TablerIconConstants.account,
  backRoute: RouteConstants.ROUTE_EXTRAS,
})

animateSwipeList(list)

</script>
