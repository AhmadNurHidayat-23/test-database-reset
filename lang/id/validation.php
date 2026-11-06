<?php
return [
 'required'=>':attribute wajib diisi.','email'=>':attribute harus berupa email yang valid.',
 'unique'=>':attribute sudah digunakan.','confirmed'=>'Konfirmasi :attribute tidak cocok.',
 'min'=>['string'=>':attribute minimal :min karakter.'], 'max'=>['string'=>':attribute maksimal :max karakter.','file'=>':attribute maksimal :max KB.'],
 'in'=>'Pilihan :attribute tidak valid.','exists'=>':attribute tidak ditemukan.','image'=>':attribute harus berupa gambar.',
 'mimes'=>':attribute harus berformat :values.','integer'=>':attribute harus berupa bilangan bulat.',
];
