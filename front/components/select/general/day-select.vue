<template>
  <app-select
    :label="$t('day_select.label')"
    :popupTitle="$t('day_select.title')"
    v-model="modelValue"
    v-model:showDropdown="showDropdown"
    :list="list"
    :columns="1"
    v-bind="dynamicAttrs"
    :has-search="false"
  >
  </app-select>
</template>

<script setup>
import Account from '~/models/Account.js'
import { useFormAttributes } from '~/composables/useFormAttributes.js'
import TablerIconConstants from '~/constants/TablerIconConstants.js'
import { format, startOfWeek, addDays } from 'date-fns'
import { capitalize } from 'lodash'

const attrs = useAttrs()
const { dynamicAttrs } = useFormAttributes(attrs)

// Internally use { value ,name }, externally emit just the value as int
const modelValue = defineModel({
  set: (value) => {
    return value.value
  },
  get: (value) => {
    return list.value.find((item) => item.value === value)
  },
})

const list = computed(() => {
  return Array.from({ length: 7 }, (_, i) => {
    const date = addDays(startOfWeek(new Date(), { weekStartsOn: 0 }), i)
    return { value: i, name: capitalize(format(date, 'EEEE')) }
  })
})

const showDropdown = ref(null)
</script>
