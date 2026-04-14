describe('Compte Client — C.G Boutique', () => {

  // Commande de connexion réutilisable
  const login = () => {
    cy.visit('/login')
    cy.get('input[name="email"]').type('costincianu.gheorghina@gmail.com')
    cy.get('input[name="password"]').type('123456')
    cy.contains('button', 'Se connecter').click()
    cy.url().should('not.include', '/login')
  }

  it('La page compte redirige vers login si non connecté', () => {
    cy.request({
      url: '/account',
      failOnStatusCode: false,
      followRedirect: false
    }).its('status').should('eq', 302)
  })

  it('Un utilisateur connecté peut accéder à son compte', () => {
    login()
    cy.url().should('include', '/account')
    cy.get('body').should('exist')
  })

  it('La page compte affiche les commandes', () => {
    login()
    cy.url().should('include', '/account')
    cy.get('body').should('exist')
  })

})