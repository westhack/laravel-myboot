<template>
  <a-card :bordered="false" style="padding: 0px">
     <search-form-generator
       ref="search"
       :defaultShowCount="1"
       :fields="searchFormData"
       @handleSubmit="handleSearch" ></search-form-generator>
    <div class="table-operator">
      <a-button type="primary" icon="plus" @click="handleCreate">新建</a-button>
      <a-button type="primary" icon="download" @click="handleDownload" :loading="downloadLoading">导出</a-button>
      <a-button type="primary" icon="reload" @click="() => { this.$refs['table'].refresh() }">刷新</a-button>
      <a-popover placement="bottom" trigger="click">
        <template slot="content">
          <a-checkbox-group @change="columnChange" v-model="columnValue">
            <a-row v-for="(column, index) in defaultColumns" :key="index"><a-checkbox :value="column.dataIndex">{{ column.title }}</a-checkbox></a-row>
          </a-checkbox-group>
          <br/>
          <div style="text-align: right;margin-top: 10px"><a-button size="small" @click="restColumn">重置</a-button></div>
        </template>
        <template slot="title">
          <span>表格列</span>
        </template>
        <a-button> <a-icon type="filter" /> 表格列</a-button>
      </a-popover>
      <a-button type="dashed" @click="tableOption">@verbatim{{ optionAlertShow && '关闭' || '开启' }}@endverbatim alert</a-button>
      <a-popconfirm v-if="selectedRowKeys.length > 0" title="确定删除选择数据?" okText="是" cancelText="否" @confirm="handleTableTopBarAction({ key: 'delete'})">
        <a-button type="danger" icon="delete" >删除</a-button>
      </a-popconfirm>

@foreach($enums as $key => $val)
      <a-dropdown v-if="selectedRowKeys.length > 0">
        <a-button style="margin-left: 8px">
           {{$val['label']}} <a-icon type="down" />
        </a-button>
        <a-menu slot="overlay">
@foreach($val['data'] as $key2 => $val2)
         <a-menu-item key="{{$val['name']}}.{{array_get($val2, 'value')}}">
           <a-popconfirm title="确定要操作么?" okText="是" cancelText="否" @confirm="handleTableTopBarAction({ key: '{{$val['name']}}', value: '{{array_get($val2, 'value')}}'})">
             <div>{{array_get($val2, 'label')}}</div>
           </a-popconfirm>
         </a-menu-item>
@endforeach
        </a-menu>
      </a-dropdown>
@endforeach

    </div>

    <a-row :gutter="8">
      <a-col :span="24">
        <a-skeleton active :loading="loading">
          <s-table
            ref="table"
            rowKey="{{$idName}}"
            bordered
            size="small"
            :columns="columns"
            :data="loadData"
            :alert="options.alert"
            :rowSelection="options.rowSelection"
            :scroll="tableScroll"
          >
            <span slot="action" slot-scope="text, record">
              <a-tag @click="handleEdit(record)">编辑</a-tag>
              <a-divider type="vertical" />
              <a-dropdown>
                <a-tag class="ant-dropdown-link">更多 <a-icon type="down" /></a-tag>
                <a-menu slot="overlay" >
                  <a-menu-item key="delete">
                    <a-popconfirm title="确定删除?" okText="是" cancelText="否" @confirm="handleTableRowBarAction('delete', record)">
                      <a-icon type="delete" /> 删除
                    </a-popconfirm>
                  </a-menu-item>
                </a-menu>
              </a-dropdown>
            </span>
          </s-table>
        </a-skeleton>
      </a-col>
    </a-row>

    <a-drawer
      title="修改"
      :width="720"
      @close="onClose"
      :visible="visible"
      :wrapStyle="{height: 'calc(100% - 108px)',overflow: 'auto',paddingBottom: '108px'}"
    >

      <form-generator
        ref="form1"
        @handleSubmit="handleSubmit"
        :fields="formData"
        :showFooter="true"
        :formLayout="formLayout"
        :action="formAction"
      >
        <template slot="footer">
          <br>
          <div
            class="drawer-form-footer"
          >
            <a-button
              type="primary"
              html-type="submit"
              :loading="submitLoading"
            >
              提交
            </a-button>
            <a-button
              :style="{ marginLeft: '8px' }"
              @click="onClose"
            >
              关闭
            </a-button>
          </div>
        </template>
      </form-generator>

    </a-drawer>

  </a-card>
</template>

<script>
import { axios } from '@/utils/request'
import { httpResponseCode } from '@/constants/httpResponseCode'
import { STable } from '@/components'
import httpResponse from '@/mixins/httpResponse'
import ATextarea from 'ant-design-vue/es/input/TextArea'
import _ from 'lodash'
import { mapFormValue } from '@/utils/util'

const defaultFormData = @php echo json_encode($defaultFormData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); @endphp

const defaultSearchFormData = @php echo json_encode($defaultSearchFormData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); @endphp

const apiList = {
 list: '{{$apiList['list']}}',
 create: '{{$apiList['create']}}',
 update: '{{$apiList['update']}}',
 detail: '{{$apiList['detail']}}',
 del: '{{$apiList['delete']}}',
 all: '{{$apiList['all']}}'
}

export default {
  name: '{{$viewClassName}}List',
  mixins: [httpResponse],
  components: {
    ATextarea,
    STable,
  },
  data () {
    const vm = this
    return {
      dict: vm.$store.getters.dict,
      tableScroll: { x: 2000 },
      loading: true,
      submitLoading: false,
      downloadLoading: false,
      formLayout: 'vertical',
      visible: false,
      openKeys: [],
      defaultSelectedKeys: [],
      selectKey: '',
      advancedSettings: [],
      formData: {},
      searchFormData: {},
      apiList: apiList,
      columns: [
@forEach($columns as $val)
@if($val['type'] == 'serial')
          {
              title: '#',
              scopedSlots: { customRender: 'serial' },
              width: '{{array_get($val, 'width')}}',
              align: '{{$val['align']}}',
              dataIndex: '{{$val['dataIndex']}}'
          },
@elseif($val['type'] == 'image')
          {
              title: '{{$val['title']}}',
              width: '{{array_get($val, 'width')}}',
              align: '{{$val['align']}}',
              dataIndex: '{{$val['dataIndex']}}',
              customRender: (text, record, index) => {
                  return <div><img style="width: 50px;height: 50px" src={record.{{$val['dataIndex']}}}/></div>
              }
          },
@elseif($val['type'] == 'select')
         {
              title: '{{$val['title']}}',
              width: '{{array_get($val, 'width')}}',
              align: '{{$val['align']}}',
              dataIndex: '{{$val['dataIndex']}}',
              customRender: (text, record, index) => {
@foreach($val['data'] as  $key => $item)
                  if (record.{{$val['dataIndex']}} == '{{$item['value']}}') {
                      return <a-tag color="{{array_get($item, 'color')}}">{{$item['label']}}</a-tag>
                  }
@endforeach
                  return record.{{$val['dataIndex']}}
              },
          },
@else
          {
              title: '{{$val['title']}}',
              width: '{{array_get($val, 'width')}}',
              align: '{{$val['align']}}',
              dataIndex: '{{$val['dataIndex']}}',
              sorter: '{{$val['sorter']}}',
          },
@endif
@endforeach
        {
          title: '操作',
          width: '150px',
          dataIndex: 'action',
          fixed: 'right',
          scopedSlots: { customRender: 'action' },
          align: 'center'
        }
      ],
      queryParam: {
        search: []
      },
      // 加载数据方法 必须为 Promise 对象
      loadData: parameter => {
        vm.pageParam = parameter
        return axios.post(vm.apiList.list, Object.assign(parameter, this.queryParam))
          .then(res => {
            vm.loading = false
            return res.data
          })
      },
      selectedRowKeys: [],
      selectedRows: [],
      // custom table alert & rowSelection
      options: {
        alert: { show: false, clear: () => { this.selectedRowKeys = [] } },
        rowSelection: {
          selectedRowKeys: this.selectedRowKeys,
          onChange: this.onSelectChange
        }
      },
      optionAlertShow: false,
      pageParam: 1,
      keyName: '{{$idName}}',
      formAction: apiList.create,
      columnValue: [],
      defaultColumns: []
    }
  },
  beforeCreate () {
    this.form = this.$form.createForm(this)
  },
  created () {
    this.loading = false
    this.searchFormData = _.cloneDeep(defaultSearchFormData)
  },
  methods: {
    tableOption () {
      this.optionAlertShow = !this.optionAlertShow
      if (this.optionAlertShow) {
        this.options = {
          alert: { show: true, clear: () => { this.selectedRowKeys = [] } },
          rowSelection: {
            selectedRowKeys: this.selectedRowKeys,
            onChange: this.onSelectChange
          }
        }
      } else {
        this.options = {
          alert: { show: false, clear: () => { this.selectedRowKeys = [] } },
          rowSelection: {
            selectedRowKeys: this.selectedRowKeys,
            onChange: this.onSelectChange
          }
        }
      }
    },

    onSelectChange (selectedRowKeys, selectedRows) {
      this.selectedRowKeys = selectedRowKeys
      this.selectedRows = selectedRows
    },

    onChange (selectedRowKeys, selectedRows) {
      this.selectedRowKeys = selectedRowKeys
      this.selectedRows = selectedRows
    },

    toggleAdvanced () {
      this.advanced = !this.advanced
    },

    onClose () {
      this.visible = false
    },

    handleCreate () {
      this.formData = _.cloneDeep(defaultFormData)
      this.formAction = this.apiList.create
      this.visible = true
    },

    handleEdit (item) {
      this.formAction = this.apiList.update
      this.formData = mapFormValue(_.cloneDeep(defaultFormData), item)
      this.visible = true
    },

    handleSubmit (res) {
      if (res.code === httpResponseCode.SUCCESS) {
        this.loadData({})
      }
    },

    handleReset () {
      this.form.resetFields()
    },

    handleChange () {
    },

    handleUpdate (val) {
      const that = this
      axios.post(this.apiList.update, val)
       .then((res) => this.submitSuccess(res))
       .catch(err => this.requestFailed(err))
       .finally(() => {
         that.$refs['table'].refresh()
       })
    },

    handleTableTopBarAction ({ key, value }) {
      if (key === 'delete') {
        this.handleDelete(this.selectedRowKeys)
      } else {
        const val = { {{$idName}}: this.selectedRowKeys }
        val[key] = value
        this.handleUpdate(val)
      }
    },

    handleTableRowBarAction (key, row) {
      if (key === 'delete') {
        this.handleDelete(row.id)
      }
    },

    handleDelete (id) {
      const that = this
      this.$confirm({
        title: '确认删除选择数据?',
        content: '删除后无法恢复',
        okText: '确认',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          axios.post(that.apiList.del, { {{$idName}}: id })
            .then((res) => this.submitSuccess(res))
            .catch(err => this.requestFailed(err))
            .finally(() => {
              that.$refs['table'].refresh()
            })
        },
        onCancel () {
        }
      })
    },

    handleSearchReset () {
      this.searchFormData = _.cloneDeep(defaultSearchFormData)
    },

    handleSearch (e) {
       e.preventDefault()
       this.$refs['search'].validateFields((err, values) => {
         this.queryParam.search = this.$refs['search'].getFieldsValue()
         this.$refs['table'].refresh(true)
       })
    },

    handleDownload () {
      this.downloadLoading = true
      import('@/utils/Export2Excel').then(excel => {
        const tHeader = []
        const filterVal = []
        this.columns.map(res => {
          tHeader.push(res.title)
          filterVal.push(res.dataIndex)
        })

        const list = this.$refs['table']['localDataSource']
        const data = this.formatJson(filterVal, list)
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: new Date().getTime(),
          autoWidth: true,
          bookType: 'xlsx'
        })
        this.downloadLoading = false
      })
    },
    formatJson (filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        return v[j]
      }))
    },

    columnChange (val) {
      const columns = []
      this.defaultColumns.map(res => {
        if (val.indexOf(res.dataIndex) != -1) {
          columns.push(res)
        }
      })

      this.columns = columns
    },
    restColumn () {
      const columns = []
      const columnValue = []
      this.defaultColumns.map(res => {
        columns.push(res)
        columnValue.push(res.dataIndex)
      })

      this.columns = columns
      this.columnValue = columnValue
    }
  }
}
</script>

<style lang="less" scoped>

</style>
