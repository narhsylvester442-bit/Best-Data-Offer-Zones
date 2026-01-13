
document.getElementById("buyForm").onsubmit = async e=>{
 e.preventDefault();
 let r = await fetch("../backend/buy.php",{
  method:"POST",
  body:new URLSearchParams({
   network:network.value,
   volume:volume.value,
   number:number.value
  })
 });
 document.getElementById("result").innerHTML = await r.text();
}
