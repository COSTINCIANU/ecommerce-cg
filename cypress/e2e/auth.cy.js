describe('Authentification — C.G Boutique', () => {

  it('La page login est accessible', () => {
    cy.visit('/login')
    cy.get('body').should('exist')
  })

  it('La page login contient un formulaire', () => {
    cy.visit('/login')
    cy.get('form').should('exist')
  })

  it('La page inscription est accessible', () => {
    cy.visit('/register')
    cy.get('body').should('exist')
  })

  it('La page inscription contient un formulaire', () => {
    cy.visit('/register')
    cy.get('form').should('exist')
  })

  it('Connexion avec mauvais identifiants affiche une erreur', () => {
    // Test Connexion 
    // ✅ Utilise avec des fauses identifiant
    cy.visit('/login')
    cy.get('input[name="email"]').type('mauvais@email.com')
    cy.get('input[name="password"]').type('mauvaismdp')
    cy.contains('button', 'Se connecter').click()
    cy.get('body').should('exist')
  })

  it('Connexion réussie redirige vers le compte', () => {
    // Connexion 
    // ✅ Utilise avec mes vrais identifiants du site
    cy.visit('/login')
    // cy.get('input[name="email"]').type('costincianu.gheorghina@gmail.com')
    // cy.get('input[name="password"]').type('123456')
    cy.get('input[name="email"]').type('test.cypress@test.com')
    cy.get('input[name="password"]').type('password123')
    cy.contains('button', 'Se connecter').click()
    cy.url().should('not.include', '/login')
  })

})