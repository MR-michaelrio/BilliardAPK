self.addEventListener("install", (event) => {
    console.log("Service Worker installed");
  });
  
  self.addEventListener("fetch", (event) => {
    // bisa ditambah cache kalau perlu
  });
  