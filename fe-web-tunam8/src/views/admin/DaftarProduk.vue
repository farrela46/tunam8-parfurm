<!-- Catalogue.vue -->
<template>
  <Navbar>
    <div>
      <div class="container-fluid px-4 py-2">
        <Breadcrumbs class="d-flex align-items-center" :items="breadcrumbsItems" />
        <v-dialog v-model="showDialog" hide-overlay persistent width="300" lazy>
          <v-progress-circular indeterminate color="red" :size="90" class="mb-0"
            style="right: -100px;"></v-progress-circular>
        </v-dialog>
        <div class="row">
          <div class="col-md-12">
            <div class="card border-0 px-2 " style="text-align: end;">
              <div class="col text-right">
                <div class="button-set my-4">
                  <v-btn color="red" rounded="xl" @click="openTags" class="mb-2 mt-2">
                    Add Tags
                  </v-btn>
                  <v-btn color="red" rounded="xl" @click="openCategory" class="mx-4 mb-2 mt-2">
                    Add Category
                  </v-btn>
                  <v-btn color="red" rounded="xl" data-bs-toggle="modal" data-bs-target="#addParfum" class="">
                    Add Parfum
                  </v-btn>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-12 mt-4 mb-12">
            <Datatables ref="datatablesParfum" />
          </div>
        </div>
      </div>
      <!-- Modal Nich -->
      <div class="modal fade text-black" id="addParfum" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Daftar Parfum</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                id="closeModal"></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="addParfum">
                <div class="mb-3">
                  <label for="exampleInputEmail1" class="form-label">Nama Parfum</label>
                  <input type="name" class="form-control" v-model="parfum.name" aria-describedby="namaCustomer">
                </div>
                <div class="mb-3">
                  <label for="exampleInputDesc" class="form-label">Deskripsi</label>
                  <textarea type="text" class="form-control" v-model="parfum.desc"
                    aria-describedby="emailCustomer"></textarea>
                </div>
                <div class="mb-3">
                  <label for="exampleKategori" class="form-label">Jenis Kelamin</label>
                  <select class="form-select" aria-label="Default select example" v-model="parfum.jenis_kelamin">
                    <option selected>Pilih jenis kelamin</option>
                    <option value="u">Unisex</option>
                    <option value="l">Laki-laki</option>
                    <option value="p">Perempuan</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Tags</label>
                  <div class="form-check" v-for="tag in tags" :key="tag.id">
                    <input class="form-check-input" type="checkbox" :value="tag.name" v-model="selectedTags">
                    <label class="form-check-label">{{ tag.name }}</label>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="exampleKategori" class="form-label">Kategori</label>
                  <select class="form-select" v-model="parfum.category">
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}
                    </option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="FileInput" class="form-label">Gambar Parfum</label>
                  <input type="file" class="form-control" ref="fileInput" @change="handleFileChange" multiple>
                </div>
                <div class="mb-3">
                  <label for="exampleInputHarga" class="form-label">Harga</label>
                  <input type="number" class="form-control" v-model="parfum.price">
                </div>
                <div class="mb-3">
                  <label for="exampleInputStock" class="form-label">Stok</label>
                  <input type="text" class="form-control" v-model="parfum.stok">
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button-custom type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button-custom>
              <button-custom class="btn btn-info" type="submit" @click="addParfum" data-bs-dismiss="modal">Tambah
                Parfum</button-custom>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Navbar>
</template>

<script>
import axios from 'axios';
import Navbar from '@/components/AdminNavbar.vue';
import Datatables from '@/components/Vuetify/DataTablesProduk.vue';
import Breadcrumbs from '@/components/Vuetify/Breadcrumbs.vue';

const BASE_URL = import.meta.env.VITE_BASE_URL_API;

export default {
  name: 'DaftarBuku',
  components: {
    Navbar,
    Datatables,
    Breadcrumbs
  },
  data() {
    return {
      showDialog: false,
      parfum: {
        name: '',
        desc: '',
        price: '',
        stok: '',
        jenis_kelamin: '',
        category: '',
        tag: ''
      },
      selectedFile: [],
      categories: [],
      tags: [],
      selectedTags: [],
      breadcrumbsItems: [
        {
          title: 'Daftar Produk',
          disabled: false,
          href: '/admin/daftarproduk',
        },
      ],
    }
  },

  mounted() {
    this.fetchCategories();
    this.retrieveTags();
  },

  methods: {
    // Prompt
    closeModal() {
      document.getElementById('closeModal').click();
    },

    clearForm() {
      this.parfum.name = '';
      this.parfum.desc = '';
      this.parfum.price = '';
      this.parfum.stok = '';
      this.parfum.foto = '';
      this.parfum.jenis_kelamin = '',
      this.parfum.category = '',
        this.$refs.fileInput.value = '';
    },
    openCategory() {
      this.$router.push('/admin/kategori');
    },
    openTags() {
      this.$router.push('/admin/tags');
    },

    // Method
    handleFileChange(event) {
      const fileInput = this.$refs.fileInput;
      this.selectedFiles = Array.from(fileInput.files);
    },

    async addParfum() {
      try {
        const formData = new FormData();
        this.selectedFiles.forEach((file, index) => {
          formData.append(`images[${index}]`, file, file.name);
        });

        formData.append('name', this.parfum.name);
        formData.append('description', this.parfum.desc);
        formData.append('price', this.parfum.price);
        formData.append('stock', this.parfum.stok);
        formData.append('tags', this.selectedTags.join(', '));
        formData.append('category_id', this.parfum.category);
        formData.append('jenis_kelamin', this.parfum.jenis_kelamin);
        document.getElementById('closeModal').click();
        const token = localStorage.getItem('access_token')
        const response = await axios.post(BASE_URL + '/products', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': 'Bearer ' + token,
          },
        });
        // $('#addParfum').modal('hide');
        this.clearForm();
        this.selectedFiles = [];
        this.selectedTags = [];
        this.$refs.datatablesParfum.retrieveParfum();

        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green',
        });
      } catch (error) {
        console.error(error);
        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          this.$notify({
            type: 'error',
            title: 'Error',
            text: errorMessage,
            color: 'red',
          });
        }
      }
    },
    async retrieveTags() {
      this.showDialog = true
      try {
        const response = await axios.get(BASE_URL + '/tags', {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        });
        this.tags = response.data.data;
      } catch (error) {
        console.error(error);
      }
    },
    async fetchCategories() {
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get(BASE_URL + '/categories', {
          headers: {
            'Authorization': 'Bearer ' + token,
          },
        });
        this.categories = response.data;
      } catch (error) {
        console.error('Error fetching categories:', error);
      } finally {
        this.showDialog = false
      }
    },
  }
};
</script>

<style scoped>
.dashboard-admin {
  min-height: 100vh;

  background: url("../../../src/assets/LandingPage/Background.png");
  background-position: center;
  background-size: cover;
}

.button-set {
  display: block;
  width: 100%;
  clear: both;
  margin: 15px 0;

}

button-custom,
.button {
  outline: 0 none;
  border: 0 none;
  padding: 13px 30px;
  background-color: #0771B8;
  background-image: linear-gradient(45deg, #ff0000 0%, #ff5252 50%, #ff7b7b 90%);
  background-position: 100% 0;
  background-size: 200% 200%;
  color: #FFF;
  font-size: 12px;
  text-transform: uppercase;
  border-radius: 20px;
  letter-spacing: 2px;
  transition: .3s;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.5s ease;

  &:hover {
    background-position: 0 0;
  }
}
</style>