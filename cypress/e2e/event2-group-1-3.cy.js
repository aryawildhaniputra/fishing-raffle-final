/**
 * Test Event 2: Grup dengan 3 Orang
 * 
 * Event ini memiliki semua grup dengan 3 anggota.
 * Total: 222 members / 3 = 74 grup
 */

describe('Event 2: Lottery Drawing - Groups of 3', () => {
  beforeEach(() => {
    cy.login()
  })

  it('should draw all available groups', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-3 Orang').click()
    
    const drawnNumbers = []
    let groupsDrawn = 0
    const maxIterations = 150
    
    // Recursive function untuk draw semua grup
    const drawAllGroups = () => {
      if (groupsDrawn >= maxIterations) {
        cy.log(`⚠ Reached max iterations (${maxIterations}), stopping`)
        return
      }
      
      cy.get('body').then($body => {
        if ($body.find('.btn-draw').length > 0) {
          cy.clickDrawButton()
          cy.waitForNumberAnimation()
          
          cy.get('#random-stall-number').invoke('text').then(number => {
            drawnNumbers.push(parseInt(number))
            groupsDrawn++
            cy.log(`Draw ${groupsDrawn}: Number ${number}`)
          })
          
          // Select position (random)
          cy.selectRandomPosition()
          
          // Wait for reload and URL to stabilize
          cy.wait(2000)
          
          // Verify we're still on event page
          cy.url().should('include', '/event/')
          
          drawAllGroups() // Recursive call
        } else {
          cy.log(`✓ All ${groupsDrawn} groups drawn!`)
        }
      })
    }
    
    drawAllGroups()
    
    // Log results
    cy.then(() => {
      cy.log(`✓ Test completed - Drew ${groupsDrawn} groups`)
      
      if (drawnNumbers.length > 0) {
        const uniqueNumbers = [...new Set(drawnNumbers)]
        cy.log(`Unique numbers used: ${uniqueNumbers.length}`)
      }
    })
    
    cy.screenshot('event2-all-groups-drawn')
  })
})
