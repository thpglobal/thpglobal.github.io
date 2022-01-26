# mcldtools.github.io
Web app for CLD Assessment

# Guiding principles
* Plain html5, css, javascript - no packages
* Use of template strings where appropriate
* Mobile first, but looks fine on laptops

# Global Objects
* cookies - an object converted from document.cookies by setup
* basics - the language components of the basic information for, from entext or frtext

# Basic Design
* Navigation icons are handmade inline svg
* Data entry goes into cookies
* All functions in the functions.js script
* Two languages so far, french and english
* Intro languages in intro_en or intro_fr
* The most "important" function is setup() that initializes each page and paints the navigation bar
* A global cookie object is decoded from document.cookie
* All the text from the spreadsheet is in entext.js or frtext.js
* All data entry pages are controled by en.html or fr.html

# Tricky bits
* When you cache files with search parameters (as this app does) you have to add a parameter, eg: caches.match(e.request,{ignoreSearch:true}).then(function (request) {...

# Key References
https://www.w3schools.com/js/js_cookies.asp
https://ourcodeworld.com/articles/read/189/how-to-create-a-file-and-generate-a-download-with-javascript-in-the-browser-without-a-server
https://vaadin.com/learn/tutorials/learn-pwa/turn-website-into-a-pwa
https://stackoverflow.com/questions/57555762/urls-like-https-example-com-page-htmlparam-val-in-offline-pwa

