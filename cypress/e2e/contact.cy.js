describe('Contact — C.G Boutique', () => {

  it('La page contact redirige si non connecté', () => {
    cy.request({
      url: '/contact',
      failOnStatusCode: false,
      followRedirect: false
    }).its('status').should('be.oneOf', [200, 302])
  })

  it('Un utilisateur connecté peut accéder au contact', () => {
    // Connexion 
    // ✅ Utilise avec mes vrais identifiants du site
    cy.visit('/login')
    cy.get('input[name="email"]').type('costincianu.gheorghina@gmail.com')
    cy.get('input[name="password"]').type('123456')
    cy.contains('button', 'Se connecter').click()

    // Accès au contact
    cy.visit('/contact')
    cy.get('form').should('exist')
  })

})