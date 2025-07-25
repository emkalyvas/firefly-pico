<template>
  <div class="app-form">
    <app-top-toolbar>
      <template #right>
        <app-button-list-add v-if="addButtonText" @click="onNew" />
      </template>
    </app-top-toolbar>

    <app-card-info v-if="itemId">
      <app-field-link :label="$t('show_transactions')" :icon="TablerIconConstants.transaction" @click="onNavigateToTransactionsList" />
    </app-card-info>

    <van-form ref="form" :name="formName" @submit="saveItem" @failed="onValidationError" class="">
      <van-cell-group inset>
        <app-field v-model="name" name="name" :label="$t('name')" rows="1" autosize :icon="TablerIconConstants.fieldText2" :rules="[rule.required()]" />

        <icon-select v-model="icon" />
      </van-cell-group>

      <div style="margin: 16px">
        <app-button-form-save />

        <app-button-form-delete class="mt-10" v-if="itemId" @click="onDelete" />
      </div>
    </van-form>
  </div>
</template>

import { ref } from 'vue';

<script setup>
import RouteConstants from '~/constants/RouteConstants'
import { useDataStore } from '~/stores/dataStore'
import _ from 'lodash'
import { useProfileStore } from '~/stores/profileStore'
import { ref } from 'vue'
import { useForm } from '~/composables/useForm'
import { generateChildren } from '~/utils/VueUtils'
import Category from '~/models/Category'
import { useToolbar } from '~/composables/useToolbar'
import CategoryTransformer from '~/transformers/CategoryTransformer'
import TablerIconConstants from '~/constants/TablerIconConstants.js'
import { rule } from '~/utils/ValidationUtils.js'

let dataStore = useDataStore()
let profileStore = useProfileStore()
const route = useRoute()

const form = ref(null)

const resetFields = () => {
  name.value = ''
}

const fetchItem = () => {
  const dataStore = useDataStore()
  item.value = dataStore.categoryDictionary[itemId.value]
}

const onEvent = (event, payload) => {
  if (event === 'onPostSave') {
    let newItem = _.get(payload, 'data.data')
    newItem = CategoryTransformer.transformFromApi(newItem)
    dataStore.categoryList = [newItem, ...dataStore.categoryList.filter((item) => item.id !== itemId.value)]
  }
  if (event === 'onPostDelete') {
    dataStore.categoryList = dataStore.categoryList.filter((item) => item.id !== itemId.value)
  }
}

let { itemId, item, isEmpty, addButtonText, isLoading, onClickBack, saveItem, onDelete, onNew, onValidationError, formName } = useForm({
  form: form,
  routeList: RouteConstants.ROUTE_CATEGORY_LIST,
  routeForm: RouteConstants.ROUTE_CATEGORY_ID,
  model: new Category(),
  resetFields: resetFields,
  fetchItem: fetchItem,
  onEvent: onEvent,
})

const { name, icon } = generateChildren(item, [
  { computed: 'name', parentKey: 'attributes.name' },
  { computed: 'icon', parentKey: `attributes.icon` },
])

watch(name, (newValue) => {
  if (profileStore.lowerCaseCategoryName) {
    newValue = newValue.toLowerCase()
  }
  if (profileStore.stripAccents) {
    newValue = LanguageUtils.removeAccents(newValue)
  }
  name.value = newValue
})

const onNavigateToTransactionsList = async () => {
  let filters = TransactionFilterUtils.filters.category.toUrl(item.value)
  await navigateTo(`${RouteConstants.ROUTE_TRANSACTION_LIST}?${filters}`)
}

const toolbar = useToolbar()
const { t } = useI18n()

toolbar.init({
  title: itemId.value ? t('category_page.title_edit') : t('category_page.title_add'),
  backRoute: RouteConstants.ROUTE_CATEGORY_LIST,
})
</script>
