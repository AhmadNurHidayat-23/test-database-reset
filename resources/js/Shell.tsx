import { useState,type ReactNode } from 'react';
import { router,usePage,Link } from '@inertiajs/react';
export default function Shell({children}:{children:ReactNode}) {
 const [dark,setDark]=useState(()=>localStorage.getItem('theme')==='dark');
 document.documentElement.dataset.theme=dark?'dark':'light';
 const {auth,flash}=usePage<{auth:{name:string}|null;flash:string|null}>().props;
 return <><header><Link className="brand" href="/"><b>05</b>DEBUG / TESTING</Link><div className="row">{auth&&<><small>{auth.name}</small><button onClick={()=>router.post('/logout')}>Keluar</button></>}<button aria-label="Ganti tema" onClick={()=>{localStorage.setItem('theme',dark?'light':'dark');setDark(!dark);}}>{dark?'Terang':'Gelap'}</button></div></header><main><div className="intro"><div><div className="eyebrow">Debugging & Testing · 05 / 05</div><h1>Test Database Reset</h1><p>Kelola satu database testing terdaftar. Buat snapshot, jalankan seeder, lalu verifikasi reset dan pemulihan dengan bukti integritas.</p></div><span className="tag">BUKTI SEBELUM KESIMPULAN.</span></div>{flash&&<div className="status" role="status">{flash}</div>}{children}<footer className="note"><span>Test Database Reset / AhmadNurHidayat-23</span><span>Ruang kerja developer.</span></footer></main></>;
}
