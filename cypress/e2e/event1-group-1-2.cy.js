/**
 * Test Event 1: Grup dengan 2 Orang
 * 
 * Event ini memiliki semua grup dengan 2 anggota.
 * Total: 222 members / 2 = 111 grup
 */

describe('Event 1: Lottery Drawing - Groups of 2', () => {
  beforeEach(() => {
    cy.login()
  })

  it('should navigate to Event 1 detail page', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-2 Orang').click()
    
    // Verify URL
    cy.url().should('include', '/event/')
    
    // Verify event name in page
    cy.contains('Event Test - Grup 1-2 Orang').should('be.visible')
  })

  it('should navigate to Pengundian tab', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-2 Orang').click()
    
    // Klik tab Pengundian
    cy.contains('button', 'Pengundian').click()
    
  })

  it('should draw ALL 111 groups', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-2 Orang').click()
    
    const drawnNumbers = []
    let groupsDrawn = 0
    const expectedGroups = 111
    const maxIterations = 120 // Safety limit
    
    // Recursive function untuk draw semua grup
    const drawAllGroups = () => {
      // Safety check: prevent infinite loop
      if (groupsDrawn >= maxIterations) {
        cy.log(`⚠ Reached max iterations (${maxIterations}), stopping`)
        return
      }
      
      cy.get('body').then($body => {
        if ($body.find('.btn-draw').length > 0) {
          cy.clickDrawButton()
          cy.waitForNumberAnimation()
          
          // Get the drawn number
          cy.get('#random-stall-number').invoke('text').then(number => {
            drawnNumbers.push(parseInt(number))
            groupsDrawn++
            cy.log(`Draw ${groupsDrawn}/111: Number ${number}`)
          })
          
          // Select position (random)
          cy.selectRandomPosition()
          
          // Wait for reload and URL to stabilize
          cy.wait(2000) // Increase to 2 seconds
          
          // Verify we're still on event page before continuing
          cy.url().should('include', '/event/')
          
          drawAllGroups() // Recursive call
        } else {
          cy.log(`✓ All ${groupsDrawn} groups drawn!`)
        }
      })
    }
    
    drawAllGroups()
    
    // Verify total groups drawn
    cy.then(() => {
      expect(groupsDrawn).to.equal(expectedGroups)
      cy.log(`✓ Successfully drew all ${expectedGroups} groups`)
      
      const uniqueNumbers = [...new Set(drawnNumbers)]
      cy.log(`Unique numbers used: ${uniqueNumbers.length}`)
    })
    
    cy.screenshot('event1-all-groups-drawn')
  })

  it('should test redraw functionality', () => {
    cy.contains('.card-title', 'Event Test - Grup 1-2 Orang').click()
    
    cy.clickDrawButton()
    cy.waitForNumberAnimation()
    
    // Get first number
    cy.get('#random-stall-number').invoke('text').then(firstNumber => {
      cy.log(`First number: ${firstNumber}`)
      
      // Klik Undi Ulang
      cy.redrawLottery()
      
      // Get second number
      cy.get('#random-stall-number').invoke('text').then(secondNumber => {
        cy.log(`Second number: ${secondNumber}`)
        cy.log(`Redraw test: ${firstNumber} -> ${secondNumber}`)
      })
      
      // Close modal without confirming (if still open)
      cy.get('body').then($body => {
        if ($body.find('#drawModal').hasClass('show')) {
          cy.get('#drawModal .btn-close').click({ force: true })
        }
      })
    })
  })
})
