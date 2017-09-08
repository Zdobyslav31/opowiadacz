var counter = 1;
var limit = 10;
function addInput(divName){
     if (counter < limit)  {
          var newdiv = document.createElement('li');
          newdiv.innerHTML ="<input type='text' class='form-control' name='myInputs[]'>";
          document.getElementById(divName).appendChild(newdiv);
          counter++;
     }
     if (counter >= limit) {
          document.getElementById('addInputButton').style.display="none"
     }
}