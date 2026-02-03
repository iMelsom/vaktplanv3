Use the following command to generate selfsigne cert: 
openssl req -x509 -newkey rsa:4096 -keyout key.pem -out cert.pem -sha256 -days 365 -nodes

Upload cert.pem to Entra ID app registration certificates and secrets
Add both cert.pem and key.pem to entra ID plugin config in Userspice installation 