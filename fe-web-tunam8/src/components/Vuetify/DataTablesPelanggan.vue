<template>
    <v-card flat>
        <v-card-title class="d-flex align-center pe-2">
            <v-icon icon="mdi-account-supervisor"></v-icon> &nbsp;
            Daftar Customer
            <v-spacer></v-spacer>
            <v-btn class="mx-3" color="primary" @click="exportTableToExcel">
                Download Data
            </v-btn>
            <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" density="compact" label="Search" single-line
                flat hide-details variant="solo-filled"></v-text-field>
        </v-card-title>

        <v-divider></v-divider>
        <v-data-table v-model:search="search" :items="items" item-value="no">
            <template v-slot:item.id="{ item }">
                <div v-if="false">{{ item.id }}</div>
            </template>
            <template v-slot:header.id></template>
            <template v-slot:item.no="{ item }">
                <div class="text-start">
                    {{ item.no }}
                </div>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-icon size="large" @click="confirmDelete(item)" color="red">
                    mdi-delete
                </v-icon>
            </template>
            <template v-slot:no-data>
                <v-btn color="primary" @click="initialize">
                    Reset
                </v-btn>
            </template>
        </v-data-table>
        <v-dialog v-model="dialogDelete" max-width="500px">
            <v-card title="Dialog">
                <v-card-text>
                    Are you sure you want to delete this item?
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="dialogDelete = false">Cancel</v-btn>
                    <v-btn color="red" @click="deleteUser(item)">OK</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script>
import axios from 'axios';
const BASE_URL = import.meta.env.VITE_BASE_URL_API;

export default {
    data() {
        return {
            search: '',
            items: [],
            dialogDelete: false,
            itemToDelete: null,
        }
    },
    computed: {
        getitems() {
            return this.items.map((item, index) => {
                return { ...item, no: index + 1 };
            });
        },
    },
    mounted() {
        this.retrieveUser();
    },
    methods: {
        exportTableToExcel() {
            const table = document.querySelector('table');
            const clonedTable = table.cloneNode(true);
            const actionHeader = Array.from(clonedTable.querySelectorAll('th')).find(th => th.textContent.trim() === 'Actions');
            const actionIndex = actionHeader ? Array.from(actionHeader.parentNode.children).indexOf(actionHeader) : -1;

            if (actionIndex !== -1) {
                clonedTable.querySelectorAll('th')[actionIndex].remove();
                clonedTable.querySelectorAll('tr').forEach(row => {
                    const cells = row.querySelectorAll('td, th');
                    if (cells[actionIndex]) {
                        cells[actionIndex].remove();
                    }
                });
            }

            const tableHTML = clonedTable.outerHTML.replace(/ /g, '%20');
            const filename = 'data.xls';
            const dataType = 'application/vnd.ms-excel';

            const downloadLink = document.createElement('a');
            document.body.appendChild(downloadLink);

            if (navigator.msSaveOrOpenBlob) {
                const blob = new Blob(['\ufeff', tableHTML], { type: dataType });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }

            document.body.removeChild(downloadLink);
        },
        confirmDelete(item) {
            this.itemToDelete = item;
            this.dialogDelete = true;
        },
        async retrieveUser() {
            try {
                const response = await axios.get(BASE_URL + '/users', {
                    headers: {
                        Authorization: "Bearer " + localStorage.getItem('access_token')
                    }
                });
                this.items = response.data.map((item, index) => ({
                    id: item.id,
                    no: index + 1,
                    nama: item.name,
                    email: item.email,
                    actions: '',
                }));
            } catch (error) {
                console.error(error);
            }
        },
        async deleteUser(item) {
            try {
                await axios.delete(BASE_URL + '/users', {
                    headers: {
                        Authorization: 'Bearer ' + localStorage.getItem('access_token'),
                    },
                    data: {
                        id: this.itemToDelete.id
                    }
                });
                this.$notify({
                    type: 'success',
                    title: 'Success',
                    text: 'Akun berhasil dihapus',
                    color: 'green'
                });
                this.retrieveUser();
                this.dialogDelete = false;
            } catch (error) {
                console.error(error);
            }
        },
    }
}
</script>
