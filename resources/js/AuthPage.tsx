import { Link,useForm } from '@inertiajs/react';
import Shell from './Shell';
export default function AuthPage({mode,can_register}:{mode:'login'|'register';can_register:boolean}) {
 const form=useForm({name:'',email:'',password:'',password_confirmation:''});
 const register=mode==='register';
 return <Shell><section className="panel auth-panel"><div className="panel-head"><h2>{register?'Buat akun':'Masuk ke ruang kerja'}</h2><span className="pill">AKSES TERLINDUNGI</span></div><form className="panel-body stack" onSubmit={e=>{e.preventDefault();form.post(register?'/register':'/login',{onSuccess:()=>form.reset('password','password_confirmation')});}}>
 {register&&<label>Nama<input autoComplete="name" required value={form.data.name} onChange={e=>form.setData('name',e.target.value)}/></label>}
 <label>Email<input type="email" autoComplete="username" required value={form.data.email} onChange={e=>form.setData('email',e.target.value)}/></label>
 <label>Password<input type="password" autoComplete={register?'new-password':'current-password'} minLength={register?10:undefined} required value={form.data.password} onChange={e=>form.setData('password',e.target.value)}/></label>
 {register&&<label>Konfirmasi password<input type="password" autoComplete="new-password" required value={form.data.password_confirmation} onChange={e=>form.setData('password_confirmation',e.target.value)}/></label>}
 {Object.values(form.errors).map((error,i)=><p className="error" role="alert" key={i}>{error}</p>)}
 <button className="primary" disabled={form.processing}>{form.processing?'Memproses…':register?'Buat akun':'Masuk'}</button>
 {register?<Link href="/login">Sudah memiliki akun? Masuk</Link>:can_register?<Link href="/register">Buat akun baru</Link>:<small>Pendaftaran pemilik sudah ditutup. Gunakan akun yang dibuat saat instalasi.</small>}
 </form></section></Shell>;
}
