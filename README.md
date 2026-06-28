1. Bootstrap
Pengertian

Bootstrap adalah framework CSS yang menyediakan berbagai komponen siap pakai sehingga proses pembuatan website menjadi lebih cepat tanpa harus menulis CSS dari awal.

Bootstrap dikembangkan oleh Twitter dan sangat populer untuk membuat website yang responsif.

Kelebihan Bootstrap
Mudah dipelajari.
Banyak komponen siap pakai.
Responsive secara otomatis.
Dokumentasi lengkap.
Cocok untuk pembuatan website dengan cepat.
Kekurangan Bootstrap
Tampilan website sering terlihat mirip.
Ukuran file cukup besar.
Sulit melakukan kustomisasi jika ingin desain yang unik.
Fitur Bootstrap
Grid System
Navbar
Button
Card
Modal
Alert
Carousel
Form
Dropdown
Pagination
Contoh Bootstrap
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
    <h1 class="text-primary">Hello Bootstrap</h1>

    <button class="btn btn-success">
        Simpan
    </button>
</div>
Grid Bootstrap

Bootstrap menggunakan sistem 12 kolom.

Contoh:

<div class="row">

    <div class="col-md-6">
        Kolom 1
    </div>

    <div class="col-md-6">
        Kolom 2
    </div>
2. Angular
Pengertian

Angular adalah framework front-end berbasis TypeScript yang dikembangkan oleh Google untuk membangun aplikasi web berskala besar dan dinamis (Single Page Application/SPA). Angular menyediakan struktur yang lengkap sehingga memudahkan pengembangan aplikasi yang kompleks.

Fungsi Angular

Angular digunakan untuk membuat:

Dashboard Admin
Sistem Informasi
E-Commerce
Sistem Akademik
Aplikasi Rumah Sakit
Media Sosial
Aplikasi Perbankan
Enterprise Application
Karakteristik Angular
Berbasis TypeScript.
Menggunakan Component.
Mendukung SPA (Single Page Application).
Menggunakan Data Binding.
Memiliki Dependency Injection.
Mendukung Routing.
Memiliki CLI (Command Line Interface).
Cara Kerja Angular
User
   │
   ▼
Component
   │
   ▼
Template (HTML)
   │
   ▼
Service
   │
   ▼
API / Database
Struktur Project Angular
my-app/

src/
│
├── app/
│   ├── home/
│   ├── login/
│   ├── dashboard/
│   ├── app.component.ts
│   ├── app.component.html
│   └── app.routes.ts
│
├── assets/
├── styles.css
└── main.ts
Komponen Utama Angular
1. Component

Bagian utama aplikasi yang menggabungkan logika (TypeScript), tampilan (HTML), dan gaya (CSS).

2. Template

File HTML yang menentukan tampilan antarmuka.

3. Module

Mengelompokkan beberapa komponen dan layanan agar mudah dikelola.

4. Service

Berisi logika bisnis atau komunikasi dengan API.

5. Routing

Mengatur perpindahan halaman tanpa memuat ulang browser.

6. Directive

Memberikan perilaku tambahan pada elemen HTML, misalnya *ngIf dan *ngFor.

7. Pipe

Memformat data yang ditampilkan, seperti tanggal, mata uang, atau huruf kapital.

Contoh Component Angular
import { Component } from '@angular/core';

@Component({
  selector: 'app-home',
  template: `
    <h1>Selamat Datang</h1>
    <p>Website Lombok Tourism</p>
  `
})
export class HomeComponent {}
Kelebihan Angular
Dikembangkan dan didukung oleh Google.
Cocok untuk aplikasi berskala besar.
Struktur proyek rapi dan konsisten.
Mendukung TypeScript sehingga lebih aman dari kesalahan kode.
Memiliki fitur bawaan seperti routing, form, dan HTTP client.
Performa baik untuk aplikasi kompleks.
Kekurangan Angular
Kurva belajar lebih tinggi dibanding Bootstrap atau Tailwind.
Ukuran proyek relatif besar.
Membutuhkan pemahaman TypeScript.
Kurang cocok untuk proyek kecil atau website sederhana.
</div>
