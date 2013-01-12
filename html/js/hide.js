function showAddr(name, dom, text) {
   addr = name + "\x40" + dom;
   addr = dom;
   if (!text) { text = addr; }
   document.write('<a href="mail'+'to'+'\x3A' + addr+'">'+text+'</a>');
}

function showEmail(name, dom, text) {
   addr = name + "\x40" + dom;
   if (!text) { text = addr; }
   document.write('<a href="mail'+'to'+'\x3A' + addr+'">'+text+'</a>');
}

function showXmpp(name, dom, text) {
   addr = name + "\x40" + dom;
   if (!text) { text = addr; }
   document.write('<a href="xm'+'pp'+'\x3A' + addr+'">'+text+'</a>');
}
