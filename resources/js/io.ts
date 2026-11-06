export function download(name: string, text: string | Blob, type = 'text/plain') {
  const url = URL.createObjectURL(text instanceof Blob ? text : new Blob([text], { type }));
  const a = document.createElement('a'); a.href = url; a.download = name; a.click();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
}
export async function readFile(file?: File): Promise<string> {
  if (!file) throw new Error('Pilih file terlebih dahulu.');
  if (file.size > 5 * 1024 * 1024) throw new Error('Batas ukuran file 5 MB.');
  return file.text();
}
export async function copy(text: string) { await navigator.clipboard.writeText(text); }
export function message(error: unknown) { return error instanceof Error ? error.message : String(error); }
