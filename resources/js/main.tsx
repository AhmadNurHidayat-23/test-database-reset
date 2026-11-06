import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import App from './App';
import AuthPage from './AuthPage';
import './style.css';
createInertiaApp({resolve:name=>name==='Auth'?AuthPage:App,setup({el,App,props}){createRoot(el).render(<App {...props}/>);}});
