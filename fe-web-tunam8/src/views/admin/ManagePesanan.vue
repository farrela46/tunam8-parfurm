<!-- Catalogue.vue -->
<template>
  <Navbar>
    <div>
      <div class="container px-4 py-2">
        <Breadcrumbs class="d-flex align-items-center" :items="breadcrumbsItems" />
        <v-dialog v-model="showDialog" hide-overlay persistent width="300" lazy>
          <v-progress-circular indeterminate color="red" :size="90" class="mb-0"
            style="right: -100px;"></v-progress-circular>
        </v-dialog>
        <div class="row ">
          <form class="">
            <div class="row d-flex justify-content-center">
              <div class="col-3">
                <div class="wrap">
                  <input type="text" id="search-bar" placeholder="Cari Pesanan" v-model="searchQuery"
                    @input="searchProduct">
                </div>
              </div>
              <div class="col-2">
                <div class="form-floating mb-3">
                  <input type="date" v-model="selectedFrom" @change="onDateChange" class="form-control"
                    id="floatingInput" placeholder="name@example.com">
                  <label for="floatingInput">From</label>
                </div>
              </div>
              <div class="col-2">
                <div class="form-floating mb-3">
                  <input type="date" v-model="selectedTo" @change="onDateChange" class="form-control" id="floatingInput"
                    placeholder="name@example.com">
                  <label for="floatingInput">To</label>
                </div>
              </div>
              <div class="col-3 d-flex align-items-center">
                <button class="btn btn-primary" @click="exportTransactionsToExcel"><span
                    class="mdi mdi-download-circle-outline"></span> Export</button>
              </div>
            </div>
          </form>
        </div>
        <div class="col-md-12 bg-white">
          <div class="row px-3">
            <div :class="{ 'col': true, 'tablist': true, 'tab-active': activeTab === 'Semua' }"
              @click="toggleTab('Semua')">
              Semua
            </div>
            <div :class="{ 'col': true, 'tablist': true, 'tab-active': activeTab === 'Belum Bayar' }"
              @click="toggleTab('Belum Bayar')">
              Belum Bayar
            </div>
            <div :class="{ 'col': true, 'tablist': true, 'tab-active': activeTab === 'Proses' }"
              @click="toggleTab('Proses')">
              Proses
            </div>
            <div :class="{ 'col': true, 'tablist': true, 'tab-active': activeTab === 'Dikirim' }"
              @click="toggleTab('Dikirim')">
              Dikirim
            </div>
            <div :class="{ 'col': true, 'tablist': true, 'tab-active': activeTab === 'Diterima' }"
              @click="toggleTab('Diterima')">
              Diterima
            </div>
            <div :class="{ 'col': true, 'tablist': true, 'tab-active': activeTab === 'Dibatalkan' }"
              @click="toggleTab('Dibatalkan')">
              Dibatalkan
            </div>
          </div>
        </div>

        <div class="col-md-12 bg-white mt-4">
          <div class="row px-3 py-2">
            <div class="col">
              <a style=" font-size: 18px;"> ID Transaction</a>
            </div>
            <div class="col">
              <a style=" font-size: 18px;"> User</a>
            </div>
            <div class="col">
              <a style=" font-size: 18px;"> Total</a>
            </div>
            <div class="col">
              <a style=" font-size: 18px;"> Tanggal</a>
            </div>
            <div class="col d-flex justify-content-center align-items-center">
              <a style=" font-size: 18px;"> Status </a>
            </div>
            <div class="col d-flex justify-content-center align-items-center">
              <a style=" font-size: 18px;"> Action </a>
            </div>
          </div>
        </div>
        <div class="col-md-12 bg-white mt-2" v-for="(transaction, index) in transactions" :key="index"
          style="cursor: pointer;">
          <div class="row px-3 ls-transaction" @click="goToTransactionDetail(transaction.id)">
            <div class="col">
              <a style="font-size: 18px; font-weight: bold;">#{{ transaction.id }}</a>
            </div>
            <div class="col">
              <a style="font-size: 18px;">{{ transaction.user.name }}</a>
            </div>
            <div class="col">
              <a style="font-size: 18px;">Rp. {{ formatPrice(transaction.total) }}</a>
            </div>
            <div class="col">
              <a style="font-size: 18px;">{{ formatDate(transaction.created_at) }}</a>
            </div>
            <div class="col d-flex justify-content-center align-items-center">
              <a :class="getClassForStatus(transaction.status)" style="font-size: 18px;">{{ transaction.status }}</a>
            </div>
            <div class="col d-flex justify-content-center align-items-cente">
              <v-icon size="large" class="me-2" @click.stop="editStatus(index)" color="blue">
                mdi-pencil-circle-outline
              </v-icon>
            </div>
          </div>
        </div>
        <v-dialog v-model="dialogForm" max-width="600">
          <v-card>
            <v-card-title>
              Update Transaction
            </v-card-title>
            <v-card-text>
              <v-select v-model="selectedStatus" :items="filteredStatusOptions" label="Status"></v-select>
              <v-text-field v-model="trackingNumber" label="Tracking Number"></v-text-field>
            </v-card-text>
            <v-card-actions>
              <v-btn color="blue darken-1" text @click="dialogForm = false">Cancel</v-btn>
              <v-btn color="blue darken-1" text @click="saveTransaction">Save</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </div>
    </div>
  </Navbar>
</template>

<script>
import Navbar from '@/components/AdminNavbar.vue';
import axios from 'axios';
const BASE_URL = import.meta.env.VITE_BASE_URL_API;
import Breadcrumbs from '@/components/Vuetify/Breadcrumbs.vue';
import moment from 'moment';

export default {
  name: 'DaftarBuku',
  components: {
    Navbar,
    Breadcrumbs
  },
  data() {
    return {
      breadcrumbsItems: [
        {
          title: 'Pesanan Saya',
          disabled: false,
          href: '/pesanan',
        },
      ],
      transactions: [],
      dialogForm: false,
      selectedTransactionIndex: null,
      selectedStatus: "",
      trackingNumber: "",
      activeTab: 'Semua',
      searchResults: [],
      searchQuery: '',
      showDialog: false,
      selectedDate: '',
      selectedFrom: '',
      selectedTo: ''
    };
  },
  computed: {
    statusOptions() {
      return ['proccess', 'canceled', 'shipping', 'received', 'unpaid'];
    },
    onDateChange() {
      this.getTransactions(this.activeTabStatus());
    },
    filteredStatusOptions() {
      const currentStatus = this.selectedStatus;
      if (currentStatus === 'unpaid') {
        return this.statusOptions;
      } else {
        return this.statusOptions.filter(status => status !== 'canceled');
      }
    },
    filteredTransactions() {
      if (this.selectedTab === 'Semua') {
        return this.transactions;
      } else {
        return this.transactions.filter(transaction => transaction.status === this.selectedTab);
      }
    }
  },
  mounted() {
    this.getTransactions();
  },
  methods: {
    exportTransactionsToExcel() {
      const dataToExport = this.transactions.map(transaction => ({
        'Transaction ID': transaction.id,
        'User': transaction.user.name,
        'Price': 'Rp. ' + this.formatPrice(transaction.total),
        'Tujuan': transaction.address && transaction.address.city ? transaction.address.city : 'OnSite',
        'Tanggal Transaksi': this.formatDate(transaction.created_at)
      }));

      const totalPrice = this.calculateTotalPrice(this.transactions);
      dataToExport.push({
        'Transaction ID': 'Total',
        'User': '',
        'Price': 'Rp. ' + this.formatPrice(totalPrice),
        'Tujuan': '',
        'Tanggal Transaksi': ''
      });

      this.exportTableToExcel(dataToExport);
    },


    exportTableToExcel(data) {
      const tableHTML = this.convertDataToHTML(data);
      const filename = 'transaction_data.xls';
      const dataType = 'application/vnd.ms-excel';

      const downloadLink = document.createElement('a');
      document.body.appendChild(downloadLink);

      if (navigator.msSaveOrOpenBlob) {
        const blob = new Blob(['\ufeff', tableHTML], { type: dataType });
        navigator.msSaveOrOpenBlob(blob, filename);
      } else {
        downloadLink.href = 'data:' + dataType + ', ' + encodeURIComponent(tableHTML);
        downloadLink.download = filename;
        downloadLink.click();
      }

      document.body.removeChild(downloadLink);
    },

    convertDataToHTML(data) {
      let table = `
    <table>
      <tr>
        <td colspan="5" style="font-weight: bold; text-align: center;">Tunam8 Perfume</td>
      </tr>
      <tr>
        <td colspan="5" style="font-weight: bold; text-align: center;">Transaction Report</td>
      </tr>
    </table>
    <table border="1" style="border-collapse:collapse;">
  `;
      const headers = Object.keys(data[0]);
      table += '<tr>';
      headers.forEach(header => {
        table += '<th style="border: 1px solid black; padding: 5px;">' + header + '</th>';
      });
      table += '</tr>';

      data.forEach((row, index) => {
        table += '<tr>';
        headers.forEach(header => {
          if (header === 'Transaction ID' && row[header] === 'Total') {
            table += '<td style="font-weight: bold; text-align: left;">' + row[header] + '</td>';
          } else {
            table += '<td style="padding: 5px;">' + row[header] + '</td>';
          }
        });
        table += '</tr>';
      });

      table += '</table>';
      return table;
    },

    calculateTotalPrice(transactions) {
      return transactions.reduce((total, transaction) => total + transaction.total, 0);
    },

    formatDate(data_date) {
      if (!data_date) return '';

      const date = new Date(data_date);
      const options = { year: 'numeric', month: 'long', day: '2-digit' };
      return date.toLocaleDateString('id-ID', options);
    },

    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },


    calculateTotalPrice(transactions) {
      return transactions.reduce((total, transaction) => total + parseFloat(transaction.total), 0);
    },
    formatDate(data_date) {
      if (!data_date) return '';

      const date = new Date(data_date);
      const options = { year: 'numeric', month: 'long', day: '2-digit' };
      return date.toLocaleDateString('id-ID', options);
    },
    newformatDate(date) {
      if (!date) return '';
      const [year, month, day] = date.split('-');
      return `${day}-${month}-${year}`;
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price);
      return numericPrice.toLocaleString('id-ID');
    },
    async getTransactions(status = '') {
      this.showDialog = true;
      try {
        const token = localStorage.getItem('access_token');

        const formattedDate = this.newformatDate(this.selectedFrom);
        const formattedFrom = this.newformatDate(this.selectedFrom);
        const formattedTo = this.newformatDate(this.selectedTo);

        let params = {
          status: status || this.activeTabStatus(),
          start_date: formattedFrom,
          end_date: formattedTo
        };

        if (formattedFrom && !formattedTo) {
          params.end_date = this.newformatDate(new Date().toISOString().split('T')[0]);
        }

        if (formattedFrom && formattedTo && formattedFrom === formattedTo) {
          params = {
            status: status || this.activeTabStatus(),
            date: formattedDate
          };
        }

        const response = await axios.get(BASE_URL + '/all-transactions', {
          params,
          headers: {
            Authorization: 'Bearer ' + token
          }
        });
        this.transactions = response.data;
      } catch (error) {
        console.error('Error fetching transactions:', error);
      } finally {
        this.showDialog = false;
      }
    },
    async searchProduct() {
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get(BASE_URL + '/search-transactions', {
          params: {
            q: this.searchQuery
          },
          headers: {
            Authorization: 'Bearer ' + token
          }
        });
        this.transactions = response.data;
      } catch (error) {
        console.error('Error searching transactions:', error);
      }
    },
    editStatus(index) {
      this.selectedTransactionIndex = index;
      this.selectedStatus = this.transactions[index].status;
      this.dialogForm = true;
    },
    saveTransaction() {
      const transactionId = this.transactions[this.selectedTransactionIndex].id;
      const requestData = {
        id: transactionId,
        status: this.selectedStatus,
        tracking_number: this.trackingNumber
      };
      axios.put(BASE_URL + '/transactions', requestData, {
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('access_token')
        }
      })
        .then(response => {
          console.log('Transaction status updated successfully:', response.data);
          this.$notify({
            type: 'success',
            title: 'Success',
            text: 'Status Updated',
            color: 'green'
          });
        })
        .catch(error => {
          console.error('Error updating transaction status:', error);
        });
      this.getTransactions();
      this.dialogForm = false;
    },
    goToTransactionDetail(id) {
      this.$router.push({ path: `/admin/pesanan/` + id });
    },
    toggleTab(tab) {
      this.activeTab = tab;
      this.getTransactions(this.activeTabStatus());
    },
    activeTabStatus() {
      switch (this.activeTab) {
        case 'Belum Bayar': return 'unpaid';
        case 'Proses': return 'proccess';
        case 'Dikirim': return 'shipping';
        case 'Diterima': return 'received';
        case 'Dibatalkan': return 'canceled';
        default: return '';
      }
    },

    getClassForStatus(status) {
      if (status === 'unpaid') {
        return 'unpaid';
      } else if (status === 'shipping') {
        return 'shipping';
      } else if (status === 'proccess') {
        return 'proccess';
      } else if (status === 'received') {
        return 'received';
      } else if (status === 'canceled') {
        return 'canceled';
      } else {
        return '';
      }
    },
  }
};



</script>

<style scoped>
/* .card-img-top {
  max-width: 450px;
  max-height: 300px;
  width: auto;
  height: auto;
} */

.ls-transaction {
  padding-top: 20px;
  padding-bottom: 20px;
}

.proccess {
  padding-right: 5px;
  padding-left: 5px;
  border-radius: 5px;
  color: black;
  font-weight: 600;
  text-decoration: none;
  background-color: yellow;
}

.shipping {
  padding-right: 5px;
  padding-left: 5px;
  border-radius: 5px;
  color: black;
  font-weight: 600;
  text-decoration: none;
  background-color: yellow;
}

.unpaid {
  padding-right: 5px;
  padding-left: 5px;
  border-radius: 5px;
  color: white;
  font-weight: 600;
  text-decoration: none;
  background-color: orange;
}

.canceled {
  padding-right: 5px;
  padding-left: 5px;
  border-radius: 5px;
  color: white;
  font-weight: 600;
  text-decoration: none;
  background-color: red;
}

.received {
  padding-right: 5px;
  padding-left: 5px;
  border-radius: 5px;
  color: 600;
  font-weight: bold;
  text-decoration: none;
  background-color: lightgreen;
}

.tablist {
  padding-top: 10px;
  padding-bottom: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
}

.tab-active {
  border-bottom: 5px solid #D0011B;
}

.shopping-cart {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.cart-items {
  flex: 2;
}

.cart-item {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.product-image {
  width: 250px;
  height: 250px;
  object-fit: contain;
  margin: auto;
  padding: 20px;
  max-width: 100%;
  height: auto;
  object-fit: contain;
}

.quantity-controls button {
  margin: 0 5px;
  padding: 5px 10px;
  cursor: pointer;
}

.selected-address {
  border: 2px solid green;
}

.cart-summary {
  flex: 1;
  padding: 20px;
}

.cart-summary h2 {
  margin-bottom: 10px;
}

.cart-summary p {
  margin-bottom: 5px;
}

.checkbox-style {
  width: 20px;
  height: 20px;
}


.search-container {
  width: 400px;
  display: block;
  margin: 0 auto;
  padding-right: 30px;
}

input#search-bar {
  margin: 0 auto;
  width: 100%;
  height: 45px;
  padding: 0 20px;
  font-size: 1rem;
  background-color: white;
  border: 1px solid #D0CFCE;
  outline: none;

  &:focus {
    border: 1px solid #D0011B;
    transition: 0.35s ease;
    color: #D0011B;

    &::-webkit-input-placeholder {
      transition: opacity 0.45s ease;
      opacity: 0;
    }

    &::-moz-placeholder {
      transition: opacity 0.45s ease;
      opacity: 0;
    }

    &:-ms-placeholder {
      transition: opacity 0.45s ease;
      opacity: 0;
    }
  }
}

.search-icon {
  position: relative;
  float: right;
  width: 75px;
  height: 75px;
  top: -62px;
  right: -70px;
}
</style>