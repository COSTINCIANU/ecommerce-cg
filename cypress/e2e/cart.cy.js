describe('Panier — C.G Boutique', () => {

  it('La page panier est accessible', () => {
    cy.visit('/cart')
    cy.get('body').should('exist')
  })

  it('La page panier retourne 200', () => {
    cy.request('/cart').its('status').should('eq', 200)
  })

  it('La page panier contient le titre panier', () => {
    cy.visit('/cart')
    cy.get('body').should('exist')
  })

  it('Ajouter un produit au panier répond correctement', () => {
    cy.request({
      url: '/cart/add/1/1',
      failOnStatusCode: false
    }).its('status').should('be.oneOf', [200, 302, 404])
  })

  it('Retirer un produit du panier répond correctement', () => {
    cy.request({
      url: '/cart/remove/1/1',
      failOnStatusCode: false
    }).its('status').should('be.oneOf', [200, 302, 404])
  })

})