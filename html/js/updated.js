function writeUpdate() {
   var mnths="Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec ";
   var update = new Date(document.lastModified);
   dd = update.getDate();
   mm = update.getMonth() * 4;
   ma = mnths.substring(mm,mm+3);
   yy = update.getYear(); yy+=(yy<70)?2000:(yy>1900)?0:1900;
   document.write(dd+" "+ma+" "+yy);
}

function writeUpdateTime() {
   var mnths="Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec ";
   var update = new Date(document.lastModified);
   dd = update.getDate();
   mm = update.getMonth();
   ma = mnths.substring(mm*4,mm*4+3);
   yy = update.getYear(); yy+=(yy<70)?2000:(yy>1900)?0:1900;
   hh = update.getHours();
   mi = update.getMinutes();
   if (mi < 10) { mi = "0"+mi; }
   document.write(dd+" "+ma+" "+yy+" "+hh+":"+mi);
}
