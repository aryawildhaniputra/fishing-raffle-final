/**
 * Test Event Import: Import Participants from Excel
 * 
 * Event ini untuk testing fitur import peserta dari file Excel.
 * Test akan menggunakan event yang sudah ada dan melakukan import data.
 */

describe('Event Import: Import Participants from Excel', () => {
  beforeEach(() => {
    cy.login()
  })

  it('should navigate to Event Import detail page', () => {
    cy.contains('.card-title', 'Event Import').click()
    
    // Verify URL
    cy.url().should('include', '/event/')
    
    // Verify event name in page
    cy.contains('Event Import').should('be.visible')
  })

  it('should navigate to Pengundian tab', () => {
    cy.contains('.card-title', 'Event Import').click()
    
    // Klik tab Pengundian
    cy.contains('button', 'Pengundian').click()
    
  })

  it('should draw all available groups', () => {
    cy.contains('.card-title', 'Event Import').click()
    
    const drawnNumbers = []
    let groupsDrawn = 0
    const maxIterations = 150 // Safety limit
    
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
            cy.log(`Draw ${groupsDrawn}: Number ${number}`)
          })
          
          // Select position (random)
          cy.selectRandomPosition()
          
          // Wait for reload and URL to stabilize
          cy.wait(2000)
          
          // Verify we're still on event page before continuing
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
    
    cy.screenshot('event-import-all-groups-drawn')
  })

  it.skip('should test redraw functionality', () => {
    cy.contains('.card-title', 'Event Import').click()
    
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
